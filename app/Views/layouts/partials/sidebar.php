<?php
$uri = (new \App\Core\Request())->uri();

/** Helper to mark active nav links. */
$active = static function (string ...$paths) use ($uri): string {
    foreach ($paths as $p) {
        if ($uri === $p || ($p !== '/' && str_starts_with($uri, $p))) {
            return 'active';
        }
    }
    return '';
};

$accountingPermissions = [
    'accounting.cash_book'   => '/accounting/cash-book',
    'accounting.bank_book'   => '/accounting/bank-book',
    'accounting.profit_loss' => '/accounting/profit-loss',
    'accounting.payables'    => '/accounting/payables',
    'accounting.receivables' => '/accounting/receivables',
];
$accountingPath = '/accounting/cash-book';
foreach ($accountingPermissions as $permission => $path) {
    if (can($permission)) {
        $accountingPath = $path;
        break;
    }
}

$nav = [
    'Main' => [
        ['dashboard', 'Dashboard', '/dashboard', 'dashboard.view'],
        ['point_of_sale', 'POS Terminal', '/pos', 'pos.use'],
        ['bolt', 'Direct Buy & Sell', '/direct-buy-sell', 'direct.view'],
    ],
    'Inventory' => [
        ['inventory_2', 'Products', '/products', 'products.view'],
        ['category', 'Categories', '/categories', 'categories.view'],
        ['sell', 'Brands', '/brands', 'brands.view'],
        ['straighten', 'Units', '/units', 'units.view'],
        ['warehouse', 'Warehouses', '/warehouses', 'warehouses.view'],
        ['layers', 'Stock', '/stock', 'stock.view'],
    ],
    'Transactions' => [
        ['shopping_cart', 'Sales', '/sales', 'sales.view'],
        ['local_shipping', 'Purchases', '/purchases', 'purchases.view'],
        ['request_quote', 'Quotations', '/quotations', 'quotations.view'],
    ],
    'People' => [
        ['groups', 'Customers', '/customers', 'customers.view'],
        ['diversity_3', 'Suppliers', '/suppliers', 'suppliers.view'],
    ],
    'Finance' => [
        ['payments', 'Expenses', '/expenses', 'expenses.view'],
        ['account_balance', 'Accounting', $accountingPath, array_keys($accountingPermissions)],
        ['analytics', 'Reports', '/reports', 'reports.view'],
    ],
    'Administration' => [
        ['manage_accounts', 'Users', '/users', 'users.view'],
        ['admin_panel_settings', 'Roles', '/roles', 'roles.view'],
        ['history', 'Activity Logs', '/activity-logs', 'logs.view'],
        ['settings', 'Settings', '/settings', 'settings.view'],
    ],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="logo-mark">N</span>
        <span class="brand-text">
            Nubia
            <small>Inventory · POS</small>
        </span>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($nav as $section => $items): ?>
            <?php
            $visible = array_filter($items, static function ($item): bool {
                $required = $item[3] ?? null;
                if (!$required) {
                    return true;
                }
                if (is_array($required)) {
                    return array_filter($required, static fn ($permission) => can($permission)) !== [];
                }
                return can($required);
            });
            if ($visible === []) {
                continue;
            }
            ?>
            <div class="nav-section-label"><?= e($section) ?></div>
            <?php foreach ($visible as $item): ?>
                <a href="<?= url($item[2]) ?>" class="nav-link <?= $active($item[2]) ?>">
                    <span class="material-symbols-rounded"><?= e($item[0]) ?></span>
                    <span><?= e($item[1]) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <script>
        (function () {
            var nav = document.currentScript && document.currentScript.previousElementSibling;
            if (!nav || !nav.classList.contains('sidebar-nav')) return;
            try {
                var saved = sessionStorage.getItem('nubia-sidebar-scroll');
                if (saved !== null) nav.scrollTop = parseInt(saved, 10) || 0;
            } catch (e) { /* ignore */ }
        })();
    </script>
</aside>
<div class="sidebar-overlay"></div>
