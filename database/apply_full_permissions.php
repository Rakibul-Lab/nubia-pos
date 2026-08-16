<?php

/**
 * Synchronize the complete fine-grained RBAC catalog and preserve existing
 * role access by expanding legacy broad grants into their equivalent actions.
 *
 * Usage: php database/apply_full_permissions.php
 */

declare(strict_types=1);

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\PermissionRegistry;

$db = Database::getInstance();
$pdo = $db->pdo();
$catalogVersion = 6;
$installedVersion = (int) ($db->scalar(
    'SELECT COALESCE(MAX(CAST(`value` AS UNSIGNED)), 0) FROM settings WHERE `key` = "rbac_catalog_version"'
) ?? 0);
$needsLegacyExpansion = $installedVersion < $catalogVersion;

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

    // A role that already owned a broad/legacy capability keeps the exact
    // actions that capability allowed before this migration.
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
    if ($needsLegacyExpansion) {
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
    }

    // Admin remains the full-access non-bypass system role.
    $pdo->exec(
        'INSERT IGNORE INTO role_permissions (role_id, permission_id)
         SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
         WHERE r.slug = "admin"'
    );

    // Give previously empty seeded roles useful least-privilege defaults.
    // Custom roles and any role already configured by the user are untouched.
    $allCatalogSlugs = PermissionRegistry::slugs();
    $defaults = [
        'manager' => array_values(array_filter(
            $allCatalogSlugs,
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
            $allCatalogSlugs,
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

    $version = $pdo->prepare(
        'INSERT INTO settings (`key`, `value`, `group`) VALUES ("rbac_catalog_version", ?, "system")
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );
    $version->execute([(string) $catalogVersion]);

    $pdo->commit();
    echo 'Synchronized ' . count(PermissionRegistry::slugs()) . " permissions.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Permission sync failed: ' . $e->getMessage() . "\n");
    exit(1);
}
