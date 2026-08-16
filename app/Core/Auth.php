<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Authentication, authorization and session identity management.
 *
 * @package App\Core
 */
final class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    /** @var array<string,mixed>|null Cached user record. */
    private static ?array $cachedUser = null;

    /** @var string[]|null Cached permission slugs. */
    private static ?array $cachedPermissions = null;

    /**
     * Attempt to authenticate with email + password.
     */
    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $db   = Database::getInstance();
        $user = $db->fetch(
            'SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Rehash if algorithm parameters changed.
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $db->update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], ['id' => $user['id']]);
        }

        self::login((int) $user['id']);

        $db->update('users', ['last_login_at' => now(), 'last_login_ip' => (new Request())->ip()], ['id' => $user['id']]);

        if ($remember) {
            self::createRememberToken((int) $user['id']);
        }

        return true;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set(self::SESSION_KEY, $userId);
        self::$cachedUser        = null;
        self::$cachedPermissions = null;
    }

    public static function logout(): void
    {
        $user = self::user();
        if ($user) {
            self::clearRememberToken((int) $user['id']);
        }
        Session::forget(self::SESSION_KEY);
        Session::destroy();
        self::$cachedUser        = null;
        self::$cachedPermissions = null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);
        if ($id !== null) {
            return (int) $id;
        }
        // Attempt remember-me cookie login.
        return self::loginFromCookie();
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function user(): ?array
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $id = self::id();
        if ($id === null) {
            return null;
        }

        $user = Database::getInstance()->fetch(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.status = "active" LIMIT 1',
            [$id]
        );

        return self::$cachedUser = $user;
    }

    /**
     * Retrieve the current user's permission slugs.
     *
     * @return string[]
     */
    public static function permissions(): array
    {
        if (self::$cachedPermissions !== null) {
            return self::$cachedPermissions;
        }

        $user = self::user();
        if (!$user) {
            return self::$cachedPermissions = [];
        }

        // Super Admin implicitly has all permissions.
        if (($user['role_slug'] ?? '') === 'super-admin') {
            return self::$cachedPermissions = ['*'];
        }

        $rows = Database::getInstance()->fetchAll(
            'SELECT p.slug FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?',
            [$user['role_id']]
        );

        return self::$cachedPermissions = array_column($rows, 'slug');
    }

    public static function can(string $permission): bool
    {
        $permissions = self::permissions();
        if (in_array('*', $permissions, true) || in_array($permission, $permissions, true)) {
            return true;
        }

        return false;
    }

    public static function hasRole(string ...$slugs): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role_slug'] ?? '', $slugs, true);
    }

    /**
     * First page the current role is actually allowed to open.
     */
    public static function landingPath(): string
    {
        $destinations = [
            'dashboard.view'          => 'dashboard',
            'pos.use'                 => 'pos',
            'products.view'           => 'products',
            'stock.view'              => 'stock',
            'sales.view'              => 'sales',
            'purchases.view'          => 'purchases',
            'quotations.view'         => 'quotations',
            'direct.view'             => 'direct-buy-sell',
            'customers.view'          => 'customers',
            'suppliers.view'          => 'suppliers',
            'expenses.view'           => 'expenses',
            'accounting.cash_book'    => 'accounting/cash-book',
            'accounting.bank_book'    => 'accounting/bank-book',
            'accounting.profit_loss'  => 'accounting/profit-loss',
            'accounting.payables'     => 'accounting/payables',
            'accounting.receivables'  => 'accounting/receivables',
            'reports.view'            => 'reports',
            'reports.sales'           => 'reports/sales',
            'reports.purchases'       => 'reports/purchases',
            'reports.profit'          => 'reports/profit',
            'reports.expenses'        => 'reports/expenses',
            'reports.stock'           => 'reports/stock',
            'reports.customers'       => 'reports/customers',
            'reports.suppliers'       => 'reports/suppliers',
            'users.view'              => 'users',
            'roles.view'              => 'roles',
            'logs.view'               => 'activity-logs',
            'settings.view'           => 'settings',
        ];
        foreach ($destinations as $permission => $path) {
            if (self::can($permission)) {
                return $path;
            }
        }
        return 'profile';
    }

    // ------------------ Remember-me tokens -----------------------

    private static function createRememberToken(int $userId): void
    {
        $selector  = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $hash      = hash('sha256', $validator);
        $expires   = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

        Database::getInstance()->insert('remember_tokens', [
            'user_id'    => $userId,
            'selector'   => $selector,
            'token_hash' => $hash,
            'expires_at' => $expires,
            'created_at' => now(),
        ]);

        setcookie('remember', $selector . ':' . $validator, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function loginFromCookie(): ?int
    {
        if (empty($_COOKIE['remember'])) {
            return null;
        }

        [$selector, $validator] = array_pad(explode(':', (string) $_COOKIE['remember'], 2), 2, '');
        if ($selector === '' || $validator === '') {
            return null;
        }

        $row = Database::getInstance()->fetch(
            'SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1',
            [$selector]
        );

        if (!$row || !hash_equals($row['token_hash'], hash('sha256', $validator))) {
            return null;
        }

        Session::set(self::SESSION_KEY, (int) $row['user_id']);
        return (int) $row['user_id'];
    }

    private static function clearRememberToken(int $userId): void
    {
        Database::getInstance()->query('DELETE FROM remember_tokens WHERE user_id = ?', [$userId]);
        if (isset($_COOKIE['remember'])) {
            setcookie('remember', '', time() - 3600, '/');
        }
    }
}
