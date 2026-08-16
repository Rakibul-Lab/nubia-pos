<?php

/**
 * Remap permission modules to sidebar-style sections.
 * Usage: php database/apply_permission_sections.php
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$env = [];
if (is_file(BASE_PATH . '/.env')) {
    foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $v = trim($v);
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'")) {
            $v = substr($v, 1, -1);
        }
        $env[trim($k)] = $v;
    }
}

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $env['DB_HOST'] ?? '127.0.0.1',
        (int) ($env['DB_PORT'] ?? 3306),
        $env['DB_NAME'] ?? 'nubia_inventory'
    ),
    $env['DB_USER'] ?? 'root',
    $env['DB_PASS'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/** @var array<string,string> slug => section */
$map = [
    'dashboard.view'     => 'Main',
    'pos.use'            => 'Main',
    'direct.use'         => 'Main',

    'products.view'      => 'Inventory',
    'products.create'    => 'Inventory',
    'products.edit'      => 'Inventory',
    'products.delete'    => 'Inventory',
    'categories.view'    => 'Inventory',
    'categories.manage'  => 'Inventory',
    'brands.view'        => 'Inventory',
    'brands.manage'      => 'Inventory',
    'units.view'         => 'Inventory',
    'units.manage'       => 'Inventory',
    'warehouses.view'    => 'Inventory',
    'warehouses.manage'  => 'Inventory',
    'stock.view'         => 'Inventory',
    'stock.manage'       => 'Inventory',

    'sales.view'         => 'Transactions',
    'sales.create'       => 'Transactions',
    'sales.delete'       => 'Transactions',
    'purchases.view'     => 'Transactions',
    'purchases.create'   => 'Transactions',
    'purchases.delete'   => 'Transactions',

    'customers.view'     => 'People',
    'customers.manage'   => 'People',
    'suppliers.view'     => 'People',
    'suppliers.manage'   => 'People',

    'expenses.view'      => 'Finance',
    'expenses.manage'    => 'Finance',
    'accounting.view'    => 'Finance',
    'reports.view'       => 'Finance',

    'users.manage'       => 'Administration',
    'roles.manage'       => 'Administration',
    'logs.view'          => 'Administration',
    'settings.manage'    => 'Administration',
];

$names = [
    'dashboard.view'    => 'View Dashboard',
    'pos.use'           => 'Use POS Terminal',
    'direct.use'        => 'Direct Buy & Sell',
    'sales.view'        => 'View Sales',
    'sales.create'      => 'Create Sales',
    'sales.delete'      => 'Delete Sales',
    'purchases.view'    => 'View Purchases',
    'purchases.create'  => 'Create Purchases',
    'purchases.delete'  => 'Delete Purchases',
    'customers.view'    => 'View Customers',
    'customers.manage'  => 'Manage Customers',
    'suppliers.view'    => 'View Suppliers',
    'suppliers.manage'  => 'Manage Suppliers',
    'expenses.view'     => 'View Expenses',
    'expenses.manage'   => 'Manage Expenses',
    'accounting.view'   => 'View Accounting',
    'reports.view'      => 'View Reports',
    'users.manage'      => 'Manage Users',
    'roles.manage'      => 'Manage Roles',
    'logs.view'         => 'View Activity Logs',
    'settings.manage'   => 'Manage Settings',
];

$upd = $pdo->prepare('UPDATE permissions SET module = ?, name = COALESCE(?, name) WHERE slug = ?');
foreach ($map as $slug => $section) {
    $name = $names[$slug] ?? null;
    $upd->execute([$section, $name, $slug]);
    echo "OK {$slug} → {$section}\n";
}

echo "Done\n";
