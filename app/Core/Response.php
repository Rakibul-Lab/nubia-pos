<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Helpers for producing HTTP responses.
 *
 * @package App\Core
 */
final class Response
{
    /**
     * Emit a JSON response and terminate.
     *
     * @param array<string,mixed> $data
     */
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Standard success envelope.
     *
     * @param array<string,mixed> $extra
     */
    public static function success(string $message, array $extra = [], int $status = 200): never
    {
        self::json(array_merge(['success' => true, 'message' => $message], $extra), $status);
    }

    /**
     * Standard error envelope.
     *
     * @param array<string,mixed> $extra
     */
    public static function error(string $message, array $extra = [], int $status = 422): never
    {
        self::json(array_merge(['success' => false, 'message' => $message], $extra), $status);
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        $messages = [
            403 => 'Forbidden - You do not have permission to access this resource.',
            404 => 'Page Not Found.',
            419 => 'Session expired. Please refresh.',
            500 => 'Internal Server Error.',
        ];
        $message = $message ?: ($messages[$status] ?? 'Error');

        View::renderError($status, $message);
        exit;
    }
}
