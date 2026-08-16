<?php

/**
 * Application route definitions.
 *
 * @var \App\Core\Router $router
 *
 * @package Nubia\Config
 */

declare(strict_types=1);

/* ----------------------------------------------------------------
 | Guest routes (authentication)
 * ---------------------------------------------------------------- */
$router->group(['guest'], function ($router): void {
    $router->get('/', 'AuthController@showLogin');
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->get('/forgot-password', 'AuthController@showForgot');
    $router->post('/forgot-password', 'AuthController@sendReset');
    $router->get('/reset-password/{token}', 'AuthController@showReset');
    $router->post('/reset-password', 'AuthController@resetPassword');
});

$router->post('/logout', 'AuthController@logout');

/* ----------------------------------------------------------------
 | Public PWA assets (no auth)
 * ---------------------------------------------------------------- */
$router->get('/manifest.webmanifest', 'PwaController@manifest');
$router->get('/browserconfig.xml', 'PwaController@browserconfig');

/* ----------------------------------------------------------------
 | Authenticated application routes
 * ---------------------------------------------------------------- */
$router->group(['auth', 'csrf'], function ($router): void {

    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/api/dashboard/chart', 'DashboardController@chartData');
    $router->get('/profile', 'ProfileController@edit');
    $router->post('/profile', 'ProfileController@update');
    $router->post('/profile/password', 'ProfileController@changePassword');

    // Global search
    $router->get('/api/search', 'SearchController@global');

    // Notifications
    $router->get('/notifications/{id}/open', 'NotificationController@open');
    $router->post('/api/notifications/{id}/read', 'NotificationController@read');
    $router->post('/api/notifications/read-all', 'NotificationController@readAll');

    // Products
    $router->get('/products', 'ProductController@index');
    $router->get('/products/export', 'ProductController@export');
    $router->get('/products/create', 'ProductController@create');
    $router->post('/products', 'ProductController@store');
    $router->get('/products/{id}', 'ProductController@show');
    $router->get('/products/{id}/edit', 'ProductController@edit');
    $router->post('/products/{id}', 'ProductController@update');
    $router->delete('/products/{id}', 'ProductController@destroy');
    $router->get('/api/products/search', 'ProductController@apiSearch');
    $router->get('/api/products/{id}/serials', 'ProductController@apiSerials');
    $router->post('/products/{id}/serials', 'ProductController@addSerials');
    $router->post('/products/{id}/serials/{serialId}', 'ProductController@updateSerial');
    $router->post('/products/{id}/serials/{serialId}/delete', 'ProductController@destroySerial');
    $router->get('/products/{id}/barcode', 'ProductController@barcode');
    $router->get('/products/{id}/qrcode', 'ProductController@qrcode');
    $router->get('/products/{id}/labels', 'ProductController@labels');
    $router->get('/products/{id}/serials/{serialId}/barcode', 'ProductController@serialBarcode');
    $router->get('/products/{id}/serials/{serialId}/qrcode', 'ProductController@serialQrcode');
    $router->get('/products/{id}/serials/{serialId}/label', 'ProductController@serialLabel');

    // Categories / Brands / Units / Warehouses
    $router->get('/categories', 'CategoryController@index');
    $router->post('/categories', 'CategoryController@store');
    $router->post('/categories/{id}', 'CategoryController@update');
    $router->delete('/categories/{id}', 'CategoryController@destroy');

    $router->get('/brands', 'BrandController@index');
    $router->post('/brands', 'BrandController@store');
    $router->post('/brands/{id}', 'BrandController@update');
    $router->delete('/brands/{id}', 'BrandController@destroy');

    $router->get('/units', 'UnitController@index');
    $router->post('/units', 'UnitController@store');
    $router->post('/units/{id}', 'UnitController@update');
    $router->delete('/units/{id}', 'UnitController@destroy');

    $router->get('/warehouses', 'WarehouseController@index');
    $router->post('/warehouses', 'WarehouseController@store');
    $router->post('/warehouses/{id}', 'WarehouseController@update');
    $router->delete('/warehouses/{id}', 'WarehouseController@destroy');

    // Stock
    $router->get('/stock', 'StockController@index');
    $router->get('/stock/adjustment', 'StockController@adjustmentForm');
    $router->post('/stock/adjustment', 'StockController@storeAdjustment');
    $router->get('/stock/transfer', 'StockController@transferForm');
    $router->post('/stock/transfer', 'StockController@storeTransfer');
    $router->get('/stock/history', 'StockController@history');

    // Customers
    $router->get('/customers', 'CustomerController@index');
    $router->get('/customers/create', 'CustomerController@create');
    $router->post('/customers', 'CustomerController@store');
    $router->get('/customers/{id}', 'CustomerController@show');
    $router->get('/customers/{id}/edit', 'CustomerController@edit');
    $router->post('/customers/{id}', 'CustomerController@update');
    $router->delete('/customers/{id}', 'CustomerController@destroy');
    $router->post('/customers/{id}/payment', 'CustomerController@collectPayment');

    // Suppliers
    $router->get('/suppliers', 'SupplierController@index');
    $router->get('/suppliers/create', 'SupplierController@create');
    $router->post('/suppliers', 'SupplierController@store');
    $router->get('/suppliers/{id}', 'SupplierController@show');
    $router->get('/suppliers/{id}/edit', 'SupplierController@edit');
    $router->post('/suppliers/{id}', 'SupplierController@update');
    $router->delete('/suppliers/{id}', 'SupplierController@destroy');
    $router->post('/suppliers/{id}/payment', 'SupplierController@makePayment');

    // Purchases
    $router->get('/purchases', 'PurchaseController@index');
    $router->get('/purchases/create', 'PurchaseController@create');
    $router->post('/purchases', 'PurchaseController@store');
    $router->get('/purchases/{id}', 'PurchaseController@show');
    $router->delete('/purchases/{id}', 'PurchaseController@destroy');
    $router->post('/purchases/{id}/payment', 'PurchaseController@addPayment');
    $router->post('/purchases/{id}/return', 'PurchaseController@returnItems');
    $router->get('/purchases/{id}/invoice', 'PurchaseController@invoice');

    // Sales / POS
    $router->get('/pos', 'PosController@index');
    $router->post('/pos/checkout', 'PosController@checkout');
    $router->get('/sales', 'SaleController@index');
    $router->get('/sales/create', 'SaleController@create');
    $router->post('/sales', 'SaleController@store');
    $router->get('/sales/{id}', 'SaleController@show');
    $router->get('/sales/{id}/exchange', 'SaleController@exchangeForm');
    $router->post('/sales/{id}/exchange', 'SaleController@exchangeStore');
    $router->delete('/sales/{id}', 'SaleController@destroy');
    $router->post('/sales/{id}/payment', 'SaleController@addPayment');
    $router->post('/sales/{id}/return', 'SaleController@returnItems');
    $router->get('/sales/{id}/invoice', 'SaleController@invoice');
    $router->get('/sales/{id}/thermal', 'SaleController@thermal');
    $router->get('/sales/{id}/pdf', 'SaleController@pdf');

    // Quotations
    $router->get('/quotations', 'QuotationController@index');
    $router->get('/quotations/create', 'QuotationController@create');
    $router->post('/quotations', 'QuotationController@store');
    $router->get('/quotations/{id}', 'QuotationController@show');

    // Direct Buy & Sell (special feature)
    $router->get('/direct-buy-sell', 'DirectBuySellController@index');
    $router->get('/direct-buy-sell/create', 'DirectBuySellController@create');
    $router->post('/direct-buy-sell', 'DirectBuySellController@store');
    $router->get('/direct-buy-sell/{id}', 'DirectBuySellController@show');
    $router->get('/direct-buy-sell/{id}/invoice', 'DirectBuySellController@invoice');

    // Expenses
    $router->get('/expenses', 'ExpenseController@index');
    $router->post('/expenses', 'ExpenseController@store');
    $router->post('/expenses/{id}', 'ExpenseController@update');
    $router->delete('/expenses/{id}', 'ExpenseController@destroy');
    $router->get('/expense-categories', 'ExpenseController@categories');
    $router->post('/expense-categories', 'ExpenseController@storeCategory');

    // Accounting
    $router->get('/accounting/cash-book', 'AccountingController@cashBook');
    $router->get('/accounting/bank-book', 'AccountingController@bankBook');
    $router->get('/accounting/profit-loss', 'AccountingController@profitLoss');
    $router->get('/accounting/payables', 'AccountingController@payables');
    $router->get('/accounting/receivables', 'AccountingController@receivables');

    // Reports
    $router->get('/reports', 'ReportController@index');
    $router->get('/reports/sales', 'ReportController@sales');
    $router->get('/reports/purchases', 'ReportController@purchases');
    $router->get('/reports/profit', 'ReportController@profit');
    $router->get('/reports/expenses', 'ReportController@expenses');
    $router->get('/reports/stock', 'ReportController@stock');
    $router->get('/reports/customers', 'ReportController@customers');
    $router->get('/reports/suppliers', 'ReportController@suppliers');
    $router->get('/reports/export/{type}', 'ReportController@export');

    // Users / Roles / Permissions
    $router->get('/users', 'UserController@index');
    $router->get('/users/create', 'UserController@create');
    $router->post('/users', 'UserController@store');
    $router->get('/users/{id}/edit', 'UserController@edit');
    $router->post('/users/{id}', 'UserController@update');
    $router->delete('/users/{id}', 'UserController@destroy');

    $router->get('/roles', 'RoleController@index');
    $router->post('/roles', 'RoleController@store');
    $router->get('/roles/{id}/permissions', 'RoleController@permissions');
    $router->post('/roles/{id}/permissions', 'RoleController@savePermissions');
    $router->delete('/roles/{id}', 'RoleController@destroy');

    // Activity logs
    $router->get('/activity-logs', 'ActivityLogController@index');

    // Settings
    $router->get('/settings', 'SettingController@index');
    $router->post('/settings', 'SettingController@update');
    $router->post('/settings/theme', 'SettingController@saveTheme');
});
