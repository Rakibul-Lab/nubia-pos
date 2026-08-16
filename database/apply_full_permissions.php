<?php

/**
 * Force the database update pass (schema + fine-grained RBAC catalog).
 *
 * The application applies this automatically on the first request after new
 * files are deployed; this script only exists for manual/CLI runs.
 *
 * Usage: php database/apply_full_permissions.php
 */

declare(strict_types=1);

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\App;
use App\Core\Updater;

App::loadEnv();

try {
    foreach (Updater::runNow() as $line) {
        echo '  ' . $line . "\n";
    }
    echo "Database is up to date (v" . Updater::VERSION . ").\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Update failed: ' . $e->getMessage() . "\n");
    exit(1);
}
