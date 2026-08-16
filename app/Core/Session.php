<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure session management wrapper.
 *
 * @package App\Core
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = config('session.lifetime', 120) * 60;

        session_name(config('session.name', 'nubia_session'));

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        // Mitigate session fixation: rotate id periodically.
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * Flash old input for form repopulation.
     *
     * @param array<string,mixed> $input
     */
    public static function flashOld(array $input): void
    {
        $filtered = $input;
        unset($filtered['password'], $filtered['password_confirmation'], $filtered['_token']);
        $_SESSION['_old'] = $filtered;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }
}
