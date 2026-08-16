<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

/**
 * Self-applying database updater.
 *
 * Brings a live database in step with the code that was just deployed: missing
 * tables, columns and indexes are created from database/schema.sql and the
 * permission catalog is re-synchronized. Every step is idempotent and nothing
 * is ever dropped or narrowed, so uploading the files is the whole deployment.
 *
 * @package App\Core
 */
final class Updater
{
    /** Bump this whenever a deployment must re-run the update pass. */
    public const VERSION = 7;

    /** Catalog version that introduced the fine-grained finance permissions. */
    private const RBAC_VERSION = 6;

    private const VERSION_KEY = 'db_version';
    private const RBAC_KEY    = 'rbac_catalog_version';

    /** Columns that changed type after release; generic sync only ever adds. */
    private const COLUMN_UPGRADES = [
        // stock_logs.note has to hold long IMEI lists.
        ['stock_logs', 'note', 'varchar', 'TEXT DEFAULT NULL'],
    ];

    /** @var string[] */
    private array $log = [];

    private function __construct(private readonly Database $db)
    {
    }

    /**
     * Run the pending update pass, if any. Never interrupts the request.
     */
    public static function maybeRun(): void
    {
        try {
            $db = Database::getInstance();
            if (self::storedVersion($db, self::VERSION_KEY) >= self::VERSION) {
                return;
            }
            (new self($db))->apply();
        } catch (Throwable $e) {
            error_log('[updater] ' . $e->getMessage());
        }
    }

    /**
     * Force a full pass and return the log (used by the CLI scripts).
     *
     * @return string[]
     */
    public static function runNow(): array
    {
        $updater = new self(Database::getInstance());
        $updater->apply(true);

        return $updater->log;
    }

    private function apply(bool $force = false): void
    {
        $pdo = $this->db->pdo();

        // Only one process may migrate at a time; the rest serve the request
        // with the current schema and pick up the change on a later hit.
        $lock = (int) $this->db->scalar("SELECT GET_LOCK(CONCAT('nubia_upd_', DATABASE()), 0)");
        if ($lock !== 1) {
            return;
        }

        try {
            if (!$force && self::storedVersion($this->db, self::VERSION_KEY) >= self::VERSION) {
                return;
            }

            $schema = $this->parseSchema();
            if ($schema !== []) {
                $this->createMissingTables($pdo, $schema);
                $this->addMissingColumns($pdo, $schema);
                $this->upgradeColumnTypes($pdo);
                $this->prepareForIndexes($pdo);
                $this->addMissingIndexes($pdo, $schema);
            }

            $this->syncPermissions($pdo);

            $this->putSetting(self::RBAC_KEY, (string) self::RBAC_VERSION);
            $this->putSetting(self::VERSION_KEY, (string) self::VERSION);
        } finally {
            $this->db->scalar("SELECT RELEASE_LOCK(CONCAT('nubia_upd_', DATABASE()))");
        }
    }

    // ---------------------------------------------------------------- schema

    /**
     * Read database/schema.sql into table definitions.
     *
     * @return array<string,array{create:string,columns:array<string,string>,indexes:array<string,string>}>
     */
    private function parseSchema(): array
    {
        $file = $this->basePath() . '/database/schema.sql';
        if (!is_file($file)) {
            return [];
        }

        $sql = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($file));
        $out = [];

        foreach (preg_split('/;\s*(?:\n|$)/', $sql) ?: [] as $statement) {
            // Section headers travel with the statement that follows them.
            $statement = trim((string) preg_replace('/\A(?:[ \t]*(?:--[^\n]*)?\n)+/', '', $statement));
            if (!preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z0-9_]+)`?\s*\(/i', $statement, $m)) {
                continue;
            }

            $lines = explode("\n", $statement);
            array_shift($lines);   // CREATE TABLE `x` (
            array_pop($lines);     // ) ENGINE=…

            $columns = [];
            $indexes = [];
            foreach ($lines as $line) {
                $line = rtrim(trim($line), ',');
                if ($line === '' || str_starts_with($line, '--')) {
                    continue;
                }
                $upper = strtoupper($line);
                if (str_starts_with($upper, 'PRIMARY KEY')
                    || str_starts_with($upper, 'CONSTRAINT')
                    || str_starts_with($upper, 'FOREIGN KEY')) {
                    continue;
                }
                if (preg_match('/^(UNIQUE\s+)?KEY\s+`([^`]+)`\s*(\(.+\))$/i', $line, $key)) {
                    $indexes[$key[2]] = (trim($key[1]) === '' ? 'KEY' : 'UNIQUE KEY')
                        . ' `' . $key[2] . '` ' . $key[3];
                    continue;
                }
                if (preg_match('/^`([^`]+)`\s+(.+)$/', $line, $col)) {
                    $columns[$col[1]] = $col[2];
                }
            }

            $out[$m[1]] = [
                'create'  => $statement,
                'columns' => $columns,
                'indexes' => $indexes,
            ];
        }

        return $out;
    }

    /**
     * @param array<string,array{create:string,columns:array<string,string>,indexes:array<string,string>}> $schema
     */
    private function createMissingTables(PDO $pdo, array $schema): void
    {
        $existing = $this->existingTables();
        $missing  = array_diff(array_keys($schema), $existing);
        if ($missing === []) {
            return;
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        try {
            // schema.sql is written in dependency order, so keep that order.
            foreach ($schema as $table => $definition) {
                if (!in_array($table, $missing, true)) {
                    continue;
                }
                $create = preg_replace(
                    '/^CREATE\s+TABLE\s+/i',
                    'CREATE TABLE IF NOT EXISTS ',
                    $definition['create'],
                    1
                ) ?? $definition['create'];

                try {
                    $pdo->exec($create);
                    $this->log[] = "created table {$table}";
                } catch (Throwable $e) {
                    $this->log[] = "table {$table} skipped: " . $e->getMessage();
                }
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * @param array<string,array{create:string,columns:array<string,string>,indexes:array<string,string>}> $schema
     */
    private function addMissingColumns(PDO $pdo, array $schema): void
    {
        $live = $this->existingColumns();

        foreach ($schema as $table => $definition) {
            if (!isset($live[$table])) {
                continue;
            }
            $previous = null;
            foreach ($definition['columns'] as $column => $type) {
                if (isset($live[$table][$column])) {
                    $previous = $column;
                    continue;
                }
                // An auto-increment column can only exist alongside its key,
                // which means the table predates this tool and needs a human.
                if (stripos($type, 'AUTO_INCREMENT') !== false) {
                    continue;
                }

                $position = '';
                if ($previous === null) {
                    $position = ' FIRST';
                } elseif (isset($live[$table][$previous])) {
                    $position = " AFTER `{$previous}`";
                }

                try {
                    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}{$position}");
                    $live[$table][$column] = true;
                    $previous = $column;
                    $this->log[] = "added {$table}.{$column}";
                } catch (Throwable $e) {
                    $this->log[] = "column {$table}.{$column} skipped: " . $e->getMessage();
                }
            }
        }
    }

    private function upgradeColumnTypes(PDO $pdo): void
    {
        foreach (self::COLUMN_UPGRADES as [$table, $column, $currentType, $target]) {
            $type = $this->db->scalar(
                'SELECT DATA_TYPE FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$table, $column]
            );
            if ($type === false || $type === null || strcasecmp((string) $type, $currentType) !== 0) {
                continue;
            }
            try {
                $pdo->exec("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$target}");
                $this->log[] = "widened {$table}.{$column}";
            } catch (Throwable $e) {
                $this->log[] = "column {$table}.{$column} not widened: " . $e->getMessage();
            }
        }
    }

    /**
     * Clean up data that would make a unique index fail.
     */
    private function prepareForIndexes(PDO $pdo): void
    {
        if (!in_array('product_serials', $this->existingTables(), true)) {
            return;
        }
        try {
            $pdo->exec("UPDATE product_serials SET imei = NULL WHERE imei = ''");
        } catch (Throwable $e) {
            $this->log[] = 'imei cleanup skipped: ' . $e->getMessage();
        }
    }

    /**
     * @param array<string,array{create:string,columns:array<string,string>,indexes:array<string,string>}> $schema
     */
    private function addMissingIndexes(PDO $pdo, array $schema): void
    {
        $live    = $this->existingIndexes();
        $columns = $this->existingColumns();

        foreach ($schema as $table => $definition) {
            if (!isset($columns[$table])) {
                continue;
            }
            foreach ($definition['indexes'] as $name => $sql) {
                if (isset($live[$table][$name])) {
                    continue;
                }
                try {
                    $pdo->exec("ALTER TABLE `{$table}` ADD {$sql}");
                    $this->log[] = "indexed {$table}.{$name}";
                } catch (Throwable $e) {
                    // Duplicate rows can block a unique index; leave the data alone.
                    $this->log[] = "index {$table}.{$name} skipped: " . $e->getMessage();
                }
            }
        }
    }

    // ----------------------------------------------------------- permissions

    private function syncPermissions(PDO $pdo): void
    {
        $tables = $this->existingTables();
        if (!in_array('permissions', $tables, true) || !in_array('role_permissions', $tables, true)) {
            return;
        }

        $needsLegacyExpansion = self::storedVersion($this->db, self::RBAC_KEY) < self::RBAC_VERSION;

        $pdo->beginTransaction();
        try {
            $upsert = $pdo->prepare(
                'INSERT INTO permissions (name, slug, module) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), module = VALUES(module)'
            );
            foreach (PermissionRegistry::catalog() as $module => $permissions) {
                foreach ($permissions as $slug => $name) {
                    $upsert->execute([$name, $slug, $module]);
                }
            }

            if ($needsLegacyExpansion) {
                $this->expandLegacyGrants($pdo);
            }

            // Admin remains the full-access non-bypass system role.
            $pdo->exec(
                'INSERT IGNORE INTO role_permissions (role_id, permission_id)
                 SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
                 WHERE r.slug = "admin"'
            );

            $this->seedRoleDefaults($pdo);

            $pdo->commit();
            $this->log[] = 'synchronized ' . count(PermissionRegistry::slugs()) . ' permissions';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * A role that already owned a broad capability keeps the exact actions
     * that capability allowed before the catalog was split up.
     */
    private function expandLegacyGrants(PDO $pdo): void
    {
        $expansions = [
            'dashboard.view'     => ['dashboard.metrics', 'dashboard.charts'],
            'pos.use'            => ['pos.checkout'],
            'direct.use'         => ['direct.view', 'direct.create', 'direct.invoice'],
            'products.view'      => ['products.search', 'products.export', 'products.labels', 'products.serials.view'],
            // Product create/edit must NOT auto-grant costs.view — cost visibility is independent.
            'products.edit'      => ['products.serials.manage'],
            'categories.manage'  => ['categories.create', 'categories.edit', 'categories.delete'],
            'brands.manage'      => ['brands.create', 'brands.edit', 'brands.delete'],
            'units.manage'       => ['units.create', 'units.edit', 'units.delete'],
            'warehouses.manage'  => ['warehouses.create', 'warehouses.edit', 'warehouses.delete'],
            'stock.view'         => ['stock.history'],
            'stock.manage'       => ['stock.adjust', 'stock.transfer'],
            'customers.manage'   => ['customers.create', 'customers.edit', 'customers.delete', 'customers.payment'],
            'suppliers.manage'   => ['suppliers.create', 'suppliers.edit', 'suppliers.delete', 'suppliers.payment'],
            'sales.view'         => ['sales.invoice', 'quotations.view'],
            'sales.create'       => ['sales.exchange', 'sales.payment', 'sales.return', 'quotations.create'],
            'purchases.view'     => ['purchases.invoice'],
            'purchases.create'   => ['purchases.payment', 'purchases.return', 'costs.view'],
            'expenses.view'      => ['expense_categories.view'],
            'expenses.manage'    => ['expenses.create', 'expenses.edit', 'expenses.delete', 'expense_categories.create'],
            'accounting.view'    => ['accounting.cash_book', 'accounting.bank_book', 'accounting.profit_loss', 'accounting.payables', 'accounting.receivables'],
            'accounting.profit_loss' => ['profit.view', 'costs.view', 'revenue.view', 'sales_total.view'],
            'accounting.payables'    => ['dues.view'],
            'accounting.receivables' => ['dues.view'],
            'reports.view'       => ['reports.sales', 'reports.purchases', 'reports.profit', 'reports.expenses', 'reports.stock', 'reports.customers', 'reports.suppliers', 'reports.export'],
            'reports.sales'      => ['revenue.view', 'sales_total.view'],
            'reports.stock'      => ['costs.view', 'stock_value.view'],
            'reports.profit'     => ['profit.view', 'costs.view', 'revenue.view', 'sales_total.view'],
            // Preserve existing access while making sales totals independently assignable.
            'revenue.view'       => ['sales_total.view'],
            'costs.view'         => ['stock_value.view'],
            'users.manage'       => ['users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign_roles', 'users.assign_super_admin', 'users.assign_warehouses'],
            'roles.manage'       => ['roles.view', 'roles.create', 'roles.permissions', 'roles.delete'],
            'settings.manage'    => ['settings.view', 'settings.edit'],
        ];

        $grant = $pdo->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id)
             SELECT rp.role_id, child.id
             FROM role_permissions rp
             JOIN permissions parent ON parent.id = rp.permission_id AND parent.slug = ?
             JOIN permissions child ON child.slug = ?'
        );
        foreach ($expansions as $legacy => $children) {
            foreach ($children as $child) {
                $grant->execute([$legacy, $child]);
            }
        }

        // Retire only known legacy broad rows after their grants have expanded.
        // Unknown/custom permissions are deliberately left untouched.
        $catalogSlugs = PermissionRegistry::slugs();
        $deleteLegacy = $pdo->prepare('DELETE FROM permissions WHERE slug = ?');
        foreach (array_keys($expansions) as $legacy) {
            if (!in_array($legacy, $catalogSlugs, true)) {
                $deleteLegacy->execute([$legacy]);
            }
        }

        // Compatibility for installations where users.manage was expanded by
        // an earlier run before assignment permissions were introduced.
        $pdo->exec(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id)
             SELECT qualified.role_id, target.id
             FROM (
                 SELECT rp.role_id
                 FROM role_permissions rp
                 JOIN permissions p ON p.id = rp.permission_id
                 WHERE p.slug IN ("users.create", "users.edit", "users.delete")
                 GROUP BY rp.role_id
                 HAVING COUNT(DISTINCT p.slug) = 3
             ) qualified
             JOIN permissions target ON target.slug IN (
                 "users.assign_roles", "users.assign_super_admin", "users.assign_warehouses"
             )'
        );

        $this->log[] = 'expanded legacy permission grants';
    }

    /**
     * Give previously empty seeded roles useful least-privilege defaults.
     * Custom roles and any role already configured by the user are untouched.
     */
    private function seedRoleDefaults(PDO $pdo): void
    {
        $all = PermissionRegistry::slugs();

        $defaults = [
            'manager' => array_values(array_filter(
                $all,
                static fn (string $slug): bool => !str_starts_with($slug, 'users.')
                    && !str_starts_with($slug, 'roles.')
                    && !str_starts_with($slug, 'settings.')
            )),
            'cashier' => [
                'dashboard.view', 'dashboard.metrics',
                'pos.use', 'pos.checkout',
                'products.view', 'products.search', 'products.serials.view',
                'sales.view', 'sales.create', 'sales.payment', 'sales.invoice',
                'customers.view', 'customers.create', 'customers.edit', 'customers.payment',
                'direct.view', 'direct.create', 'direct.invoice',
            ],
            'salesman' => [
                'dashboard.view', 'products.view', 'products.search', 'products.serials.view',
                'sales.view', 'sales.create', 'sales.payment', 'sales.invoice',
                'quotations.view', 'quotations.create',
                'customers.view', 'customers.create', 'customers.edit',
            ],
            'accountant' => [
                'dashboard.view', 'dashboard.metrics', 'dashboard.charts',
                'costs.view', 'profit.view', 'sales_total.view', 'revenue.view', 'stock_value.view', 'dues.view',
                'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
                'expense_categories.view', 'expense_categories.create',
                'accounting.cash_book', 'accounting.bank_book', 'accounting.profit_loss',
                'accounting.payables', 'accounting.receivables',
                'reports.view', 'reports.sales', 'reports.purchases', 'reports.profit',
                'reports.expenses', 'reports.stock', 'reports.customers',
                'reports.suppliers', 'reports.export',
            ],
            'store-keeper' => array_values(array_filter(
                $all,
                static fn (string $slug): bool => $slug === 'dashboard.view'
                    || $slug === 'dashboard.metrics'
                    || $slug === 'costs.view'
                    || $slug === 'stock_value.view'
                    || str_starts_with($slug, 'products.')
                    || str_starts_with($slug, 'categories.')
                    || str_starts_with($slug, 'brands.')
                    || str_starts_with($slug, 'units.')
                    || str_starts_with($slug, 'warehouses.')
                    || str_starts_with($slug, 'stock.')
                    || str_starts_with($slug, 'purchases.')
                    || str_starts_with($slug, 'suppliers.')
            )),
            'employee' => ['dashboard.view'],
        ];

        $roleCount = $pdo->prepare(
            'SELECT r.id, COUNT(rp.permission_id) AS permission_count
             FROM roles r LEFT JOIN role_permissions rp ON rp.role_id = r.id
             WHERE r.slug = ? GROUP BY r.id'
        );
        $grantDefault = $pdo->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id)
             SELECT ?, id FROM permissions WHERE slug = ?'
        );

        foreach ($defaults as $roleSlug => $slugs) {
            $roleCount->execute([$roleSlug]);
            $role = $roleCount->fetch(PDO::FETCH_ASSOC);
            if (!$role || (int) $role['permission_count'] !== 0) {
                continue;
            }
            foreach ($slugs as $slug) {
                $grantDefault->execute([(int) $role['id'], $slug]);
            }
        }
    }

    // --------------------------------------------------------------- helpers

    /** @return string[] */
    private function existingTables(): array
    {
        $rows = $this->db->fetchAll(
            'SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'
        );

        return array_column($rows, 'name');
    }

    /** @return array<string,array<string,bool>> */
    private function existingColumns(): array
    {
        $rows = $this->db->fetchAll(
            'SELECT TABLE_NAME AS t, COLUMN_NAME AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()'
        );

        $out = [];
        foreach ($rows as $row) {
            $out[$row['t']][$row['c']] = true;
        }

        return $out;
    }

    /** @return array<string,array<string,bool>> */
    private function existingIndexes(): array
    {
        $rows = $this->db->fetchAll(
            'SELECT TABLE_NAME AS t, INDEX_NAME AS i FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()'
        );

        $out = [];
        foreach ($rows as $row) {
            $out[$row['t']][$row['i']] = true;
        }

        return $out;
    }

    private function putSetting(string $key, string $value): void
    {
        $this->db->query(
            'INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, "system")
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value]
        );
    }

    private static function storedVersion(Database $db, string $key): int
    {
        try {
            $value = $db->scalar('SELECT `value` FROM settings WHERE `key` = ? LIMIT 1', [$key]);
        } catch (Throwable) {
            // A database that predates the settings table is simply at zero.
            return 0;
        }

        return $value === false || $value === null ? 0 : (int) $value;
    }

    private function basePath(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
    }
}
