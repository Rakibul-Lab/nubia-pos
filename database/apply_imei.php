<?php

declare(strict_types=1);

/**
 * Apply IMEI tracking schema updates.
 * Usage: php database/apply_imei.php
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

$envFile = $root . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'nubia_inventory';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Applying IMEI schema updates to {$name}…\n";

$cols = $pdo->query("SHOW COLUMNS FROM product_serials")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('sale_item_id', $cols, true)) {
    $pdo->exec('ALTER TABLE `product_serials` ADD COLUMN `sale_item_id` INT UNSIGNED DEFAULT NULL AFTER `warehouse_id`');
    echo "  + sale_item_id\n";
}
if (!in_array('purchase_item_id', $cols, true)) {
    $pdo->exec('ALTER TABLE `product_serials` ADD COLUMN `purchase_item_id` INT UNSIGNED DEFAULT NULL AFTER `sale_item_id`');
    echo "  + purchase_item_id\n";
}

$indexes = $pdo->query("SHOW INDEX FROM product_serials")->fetchAll(PDO::FETCH_ASSOC);
$names = array_unique(array_column($indexes, 'Key_name'));
if (!in_array('uq_product_serials_imei', $names, true)) {
    // Clear empty-string IMEIs so UNIQUE works cleanly.
    $pdo->exec("UPDATE product_serials SET imei = NULL WHERE imei = ''");
    try {
        $pdo->exec('ALTER TABLE `product_serials` ADD UNIQUE KEY `uq_product_serials_imei` (`imei`)');
        echo "  + unique imei\n";
    } catch (Throwable $e) {
        echo "  ! unique imei skipped: " . $e->getMessage() . "\n";
    }
}
if (!in_array('idx_serial_sale_item', $names, true)) {
    $pdo->exec('ALTER TABLE `product_serials` ADD KEY `idx_serial_sale_item` (`sale_item_id`)');
    echo "  + idx_serial_sale_item\n";
}

$logCols = $pdo->query("SHOW COLUMNS FROM stock_logs")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('imei_codes', $logCols, true)) {
    $pdo->exec('ALTER TABLE `stock_logs` ADD COLUMN `imei_codes` TEXT DEFAULT NULL AFTER `note`');
    echo "  + stock_logs.imei_codes\n";
}
// Allow longer notes when many IMEIs are listed.
$noteType = $pdo->query("SHOW COLUMNS FROM stock_logs LIKE 'note'")->fetch(PDO::FETCH_ASSOC);
if ($noteType && stripos((string) ($noteType['Type'] ?? ''), 'varchar') !== false) {
    $pdo->exec('ALTER TABLE `stock_logs` MODIFY COLUMN `note` TEXT DEFAULT NULL');
    echo "  ~ stock_logs.note → TEXT\n";
}

echo "Done.\n";
