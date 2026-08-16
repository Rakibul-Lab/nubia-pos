<?php

/**
 * Nubia Inventory - Front Controller.
 *
 * All HTTP requests are routed through this single entry point.
 *
 * @package Nubia
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// When using `php -S … -t public public/index.php`, static files must be
// handed back to the built-in server; otherwise CSS/JS/images return 404.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = rawurldecode($path);

    if ($path !== '/' && $path !== '') {
        $file = __DIR__ . $path;
        $publicRoot = realpath(__DIR__);

        if ($publicRoot && is_file($file)) {
            $realFile = realpath($file);
            if ($realFile && str_starts_with($realFile, $publicRoot . DIRECTORY_SEPARATOR)) {
                return false;
            }
        }
    }
}

require BASE_PATH . '/vendor/autoload.php';

(new App\Core\App())->boot();
