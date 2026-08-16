<?php

/**
 * Verify that every authenticated route has an explicit RBAC policy and that
 * every configured policy points to a real controller method.
 *
 * Usage: php database/audit_permissions.php
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

use App\Core\PermissionRegistry;
use App\Core\Database;

$source = file_get_contents(BASE_PATH . '/config/routes.php') ?: '';
preg_match_all(
    '/\$router->(?:get|post|put|delete)\([^,]+,\s*[\'"]([^\'"]+Controller@[^\'"]+)[\'"]\)/',
    $source,
    $matches
);

$public = [
    'AuthController@showLogin',
    'AuthController@login',
    'AuthController@showForgot',
    'AuthController@sendReset',
    'AuthController@showReset',
    'AuthController@resetPassword',
    'AuthController@logout',
    'PwaController@manifest',
    'PwaController@browserconfig',
];

$errors = [];
$routeHandlers = array_unique($matches[1]);
foreach ($routeHandlers as $handler) {
    if (in_array($handler, $public, true)) {
        continue;
    }
    if (PermissionRegistry::forHandler($handler) === null && !PermissionRegistry::isSelfService($handler)) {
        $errors[] = "Missing policy: {$handler}";
    }

    [$controller, $method] = explode('@', $handler, 2);
    $class = 'App\\Controllers\\' . $controller;
    if (!class_exists($class) || !method_exists($class, $method)) {
        $errors[] = "Invalid handler: {$handler}";
    }
}

$catalogSlugs = PermissionRegistry::slugs();
$policyConfig = require BASE_PATH . '/config/permissions.php';
foreach (($policyConfig['handlers'] ?? []) as $handler => $permission) {
    if (!in_array($handler, $routeHandlers, true)) {
        $errors[] = "Policy has no route: {$handler}";
    }
    if (!in_array($permission, $catalogSlugs, true)) {
        $errors[] = "Policy uses unknown permission: {$handler} => {$permission}";
    }

    [$controller, $method] = explode('@', $handler, 2);
    $class = 'App\\Controllers\\' . $controller;
    if (class_exists($class) && method_exists($class, $method)) {
        $reflection = new ReflectionMethod($class, $method);
        $lines = file($reflection->getFileName()) ?: [];
        $body = implode('', array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - $reflection->getStartLine() + 1
        ));
        preg_match_all('/authorize\\([\'"]([^\'"]+)[\'"]\\)/', $body, $authMatches);
        if ($authMatches[1] !== [] && !in_array($permission, $authMatches[1], true)) {
            $errors[] = "Controller check conflicts with policy: {$handler} => {$permission}";
        }
    }
}

$dbSlugs = Database::getInstance()->fetchAll('SELECT slug FROM permissions');
$dbSlugs = array_column($dbSlugs, 'slug');
foreach (array_diff($catalogSlugs, $dbSlugs) as $slug) {
    $errors[] = "Catalog permission missing from database: {$slug}";
}
$retired = [
    'direct.use', 'categories.manage', 'brands.manage', 'units.manage',
    'warehouses.manage', 'stock.manage', 'customers.manage', 'suppliers.manage',
    'expenses.manage', 'accounting.view', 'users.manage', 'roles.manage',
    'settings.manage',
];
foreach (array_intersect($retired, $dbSlugs) as $slug) {
    $errors[] = "Retired broad permission still exists: {$slug}";
}

if ($errors !== []) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

echo 'OK: ' . (count($routeHandlers) - count($public))
    . ' authenticated route handlers and ' . count($catalogSlugs)
    . " permissions are covered.\n";
