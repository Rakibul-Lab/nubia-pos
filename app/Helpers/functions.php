<?php

/**
 * Global helper functions for Nubia Inventory.
 *
 * These are loaded via Composer's "files" autoload so they are available
 * everywhere without an import statement.
 *
 * @package Nubia\Helpers
 */

declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * Read an environment variable loaded from the .env file.
     */
    function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        // Normalise common literals.
        return match (strtolower((string) $value)) {
            'true', '(true)'   => 'true',
            'false', '(false)' => 'false',
            'null', '(null)'   => $default,
            'empty', '(empty)' => '',
            default            => (string) $value,
        };
    }
}

if (!function_exists('config')) {
    /**
     * Retrieve a configuration value using dot notation, e.g. config('app.name').
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $config = require BASE_PATH . '/config/config.php';
        }

        $segments = explode('.', $key);
        $value    = $config;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('app_base_url')) {
    /**
     * Resolve the application base URL for the current request.
     * Uses the incoming Host header in web requests so localhost and 127.0.0.1
     * stay consistent (avoids CSRF/session cookie mismatches in local dev).
     */
    function app_base_url(): string
    {
        if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $scheme . '://' . $_SERVER['HTTP_HOST'];
        }

        return rtrim((string) config('app.url'), '/');
    }
}

if (!function_exists('url')) {
    /**
     * Build an absolute URL relative to the application base URL.
     */
    function url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        $base = app_base_url();

        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('asset_v')) {
    /**
     * Asset URL cache-busted by file modification time, so edits are never
     * masked by a stale browser cache.
     */
    function asset_v(string $path): string
    {
        $path = ltrim($path, '/');
        $file = dirname(__DIR__, 2) . '/public/assets/' . $path;
        $stamp = is_file($file) ? (string) filemtime($file) : (string) config('app.version');

        return asset($path) . '?v=' . $stamp;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        $location = str_starts_with($path, 'http') ? $path : url($path);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve previously submitted input flashed to the session.
     */
    function old(string $key, mixed $default = ''): mixed
    {
        $old = $_SESSION['_old'][$key] ?? $default;
        return is_string($old) ? e($old) : $old;
    }
}

if (!function_exists('e')) {
    /**
     * Escape a string for safe HTML output (XSS protection).
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('auth')) {
    /**
     * Get the currently authenticated user (array) or null.
     */
    function auth(): ?array
    {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('can')) {
    /**
     * Determine whether the current user has a permission.
     */
    function can(string $permission): bool
    {
        return \App\Core\Auth::can($permission);
    }
}

if (!function_exists('strip_product_costs')) {
    /**
     * Remove cost / buying-price fields from product payloads for unauthorized users.
     *
     * @param array<int|string,mixed> $row
     * @return array<int|string,mixed>
     */
    function strip_product_costs(array $row, bool $allowPurchaseDefault = false): array
    {
        if (\App\Core\Auth::can('costs.view')) {
            return $row;
        }
        if ($allowPurchaseDefault && \App\Core\Auth::can('purchases.create')) {
            return $row;
        }
        unset($row['cost_price'], $row['cost'], $row['unit_cost'], $row['total_cost']);
        return $row;
    }
}

if (!function_exists('strip_product_costs_list')) {
    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array<int,array<string,mixed>>
     */
    function strip_product_costs_list(array $rows, bool $allowPurchaseDefault = false): array
    {
        foreach ($rows as &$row) {
            if (is_array($row)) {
                $row = strip_product_costs($row, $allowPurchaseDefault);
            }
        }
        unset($row);
        return $rows;
    }
}

if (!function_exists('flash')) {
    /**
     * Set or get a one-time flash message.
     */
    function flash(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $_SESSION['_flash'] ?? [];
        }

        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }

        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

if (!function_exists('money')) {
    /**
     * Format a number as currency using business settings.
     */
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $amount = (float) $amount;
        $symbol = config('business.currency_symbol', 'Tk');
        $formatted = number_format($amount, 2);
        return $withSymbol ? $symbol . ' ' . $formatted : $formatted;
    }
}

if (!function_exists('money_inc_vat')) {
    /**
     * Format money with an "inc.vat" suffix (prices are VAT-inclusive).
     */
    function money_inc_vat(float|int|string|null $amount, bool $withSymbol = true): string
    {
        return money($amount, $withSymbol) . ' <span class="inc-vat">inc.vat</span>';
    }
}

if (!function_exists('payment_methods')) {
    /**
     * @return array<string,string> value => label
     */
    function payment_methods(bool $includeCredit = true): array
    {
        $methods = [
            'cash'         => 'Cash',
            'card'         => 'Card',
            'bank'         => 'Bank',
            'bkash'        => 'bKash',
            'nagad'        => 'Nagad',
            'upay'         => 'Upay',
            'dutch_bangla' => 'Dutch Bangla',
            'mobile'       => 'Mobile Banking',
        ];
        if ($includeCredit) {
            $methods['credit'] = 'Credit';
        }
        return $methods;
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        $methods = payment_methods(true);
        $methods['mixed'] = 'Mixed';
        $methods['cheque'] = 'Cheque';
        $key = strtolower((string) $method);
        return $methods[$key] ?? ucfirst(str_replace('_', ' ', (string) $method));
    }
}

if (!function_exists('setting')) {
    /**
     * Read a persisted application setting from the settings table (cached).
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $settings = null;

        if ($settings === null) {
            $settings = [];
            try {
                $rows = \App\Core\Database::getInstance()
                    ->query('SELECT `key`, `value` FROM settings')
                    ->fetchAll();
                foreach ($rows as $row) {
                    $settings[$row['key']] = $row['value'];
                }
            } catch (\Throwable) {
                $settings = [];
            }
        }

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (debugging helper).
     */
    function dd(mixed ...$vars): never
    {
        echo '<pre style="background:#0f172a;color:#e2e8f0;padding:16px;border-radius:8px;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('now')) {
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? '';
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
        $text = strtolower($text);
        return preg_replace('~[^-\w]+~', '', $text) ?: 'n-' . uniqid();
    }
}

if (!function_exists('generate_code')) {
    /**
     * Generate a prefixed sequential-style reference code.
     */
    function generate_code(string $prefix): string
    {
        return strtoupper($prefix) . '-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }
}
