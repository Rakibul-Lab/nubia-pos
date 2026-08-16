<?php

/**
 * Application configuration.
 *
 * Values are pulled from the .env file (when present) and fall back to
 * sensible defaults. Access anywhere via config('key.subkey').
 *
 * @package Nubia\Config
 */

declare(strict_types=1);

return [

    'app' => [
        'name'      => env('APP_NAME', 'Nubia Inventory'),
        'env'       => env('APP_ENV', 'production'),
        'debug'     => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'url'       => rtrim(env('APP_URL', 'http://localhost:8000'), '/'),
        'timezone'  => env('APP_TIMEZONE', 'Asia/Dhaka'),
        'locale'    => env('APP_LOCALE', 'en'),
        'key'       => env('APP_KEY', 'nubia-default-insecure-key-change-me'),
        'version'   => '1.2.1',
    ],

    'database' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', 'nubia_inventory'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],

    'session' => [
        'name'     => env('SESSION_NAME', 'nubia_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', '120'), // minutes
    ],

    'mail' => [
        'host'       => env('MAIL_HOST', 'localhost'),
        'port'       => (int) env('MAIL_PORT', '587'),
        'username'   => env('MAIL_USERNAME', ''),
        'password'   => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_addr'  => env('MAIL_FROM_ADDRESS', 'no-reply@nubiainventory.com'),
        'from_name'  => env('MAIL_FROM_NAME', 'Nubia Inventory'),
    ],

    'business' => [
        'currency'        => env('DEFAULT_CURRENCY', 'BDT'),
        'currency_symbol' => env('DEFAULT_CURRENCY_SYMBOL', 'Tk'),
    ],

    // Path helpers (absolute).
    'paths' => [
        'base'    => BASE_PATH,
        'app'     => BASE_PATH . '/app',
        'views'   => BASE_PATH . '/app/Views',
        'storage' => BASE_PATH . '/storage',
        'uploads' => BASE_PATH . '/storage/uploads',
        'logs'    => BASE_PATH . '/storage/logs',
        'public'  => BASE_PATH . '/public',
    ],
];
