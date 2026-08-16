<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF token generation and verification.
 *
 * @package App\Core
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public static function verify(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::KEY], $token);
    }

    /**
     * Validate the token from a request or abort with 419.
     */
    public static function check(Request $request): void
    {
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

        if (!self::verify($token)) {
            http_response_code(419);
            if ($request->wantsJson()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'CSRF token mismatch. Please refresh and try again.']);
            } else {
                echo 'CSRF token mismatch. Please refresh the page and try again.';
            }
            exit;
        }
    }
}
