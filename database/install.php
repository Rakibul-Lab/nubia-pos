<?php

/**
 * Nubia Inventory - Database installer.
 *
 * Runs schema.sql then seed.sql against the configured database.
 * Usage:  php database/install.php
 *
 * Reads DB credentials from the .env file (or falls back to defaults).
 *
 * @package Nubia\Database
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// -- Minimal .env parser (installer runs standalone) ----------
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

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($env['DB_PORT'] ?? 3306);
$name = $env['DB_NAME'] ?? 'nubia_inventory';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

fwrite(STDOUT, "Nubia Inventory installer\n");
fwrite(STDOUT, "-------------------------\n");
fwrite(STDOUT, "Host: {$host}:{$port}  DB: {$name}  User: {$user}\n\n");

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Cannot connect to MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

$run = static function (string $path) use ($pdo): void {
    if (!is_file($path)) {
        fwrite(STDERR, "Missing SQL file: {$path}\n");
        exit(1);
    }
    $sql = file_get_contents($path) ?: '';
    // Execute the whole file (multi-statement).
    $pdo->exec($sql);
    fwrite(STDOUT, "  ✓ Executed " . basename($path) . "\n");
};

fwrite(STDOUT, "Importing schema...\n");
$run(BASE_PATH . '/database/schema.sql');

fwrite(STDOUT, "Importing seed data...\n");
$run(BASE_PATH . '/database/seed.sql');

fwrite(STDOUT, "Synchronizing fine-grained permissions...\n");
require BASE_PATH . '/database/apply_full_permissions.php';

fwrite(STDOUT, "\nDone! Database '{$name}' is ready.\n");
fwrite(STDOUT, "Login: admin@nubia.test  /  admin123\n");
