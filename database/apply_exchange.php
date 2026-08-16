<?php

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

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($env['DB_PORT'] ?? 3306);
$name = $env['DB_NAME'] ?? 'nubia_inventory';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name),
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$sql = file_get_contents(__DIR__ . '/alter_exchange.sql');
$parts = preg_split('/;\s*(?=\n|$)/', $sql) ?: [];

foreach ($parts as $stmt) {
    $lines = array_filter(explode("\n", $stmt), static function (string $line): bool {
        $line = trim($line);
        return $line !== '' && !str_starts_with($line, '--');
    });
    $stmt = trim(implode("\n", $lines));
    if ($stmt === '') {
        continue;
    }
    try {
        $pdo->exec($stmt);
        echo 'OK: ' . substr(preg_replace('/\s+/', ' ', $stmt) ?? $stmt, 0, 70) . "...\n";
    } catch (Throwable $e) {
        echo 'ERR: ' . $e->getMessage() . "\n";
    }
}

echo "Done\n";
