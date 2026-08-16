<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Application bootstrapper.
 *
 * @package App\Core
 */
final class App
{
    public function boot(): void
    {
        // .env MUST load before any config() call so cached config sees real values.
        $this->loadEnv();
        $this->configureErrors();

        date_default_timezone_set(config('app.timezone', 'UTC'));

        Session::start();
        $this->securityHeaders();

        // Make current user available to all views.
        View::share('currentUser', Auth::user());

        $router = new Router();
        require config('paths.base') . '/config/routes.php';

        $router->dispatch(new Request());
    }

    /**
     * Parse the .env file into $_ENV.
     */
    private function loadEnv(): void
    {
        // Use the BASE_PATH constant directly; calling config() here would
        // prematurely cache configuration before env vars are available.
        $file = BASE_PATH . '/.env';
        if (!is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key   = trim($key);
            $value = trim($value);
            // Strip surrounding quotes.
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'")) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    private function configureErrors(): void
    {
        $debug = config('app.debug', false);

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        $logDir = config('paths.logs', BASE_PATH . '/storage/logs');
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        ini_set('error_log', $logDir . '/php-error.log');

        set_exception_handler(function (\Throwable $e) use ($debug): void {
            error_log((string) $e);
            http_response_code(500);
            if ($debug) {
                echo '<pre style="padding:20px;font-family:monospace;">';
                echo e($e->getMessage()) . "\n\n" . e($e->getTraceAsString());
                echo '</pre>';
            } else {
                View::renderError(500, 'Something went wrong. Please try again later.');
            }
            exit;
        });
    }

    private function securityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 1; mode=block');
    }
}
