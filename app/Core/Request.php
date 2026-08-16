<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Encapsulates an incoming HTTP request.
 *
 * @package App\Core
 */
final class Request
{
    /** @var array<string,mixed> */
    private array $query;

    /** @var array<string,mixed> */
    private array $body;

    /** @var array<string,mixed> */
    private array $files;

    public function __construct()
    {
        $this->query = $_GET;
        $this->files = $_FILES;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw        = file_get_contents('php://input') ?: '';
            $decoded    = json_decode($raw, true);
            $this->body = is_array($decoded) ? $decoded : [];
        } else {
            $this->body = $_POST;
        }
    }

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Method spoofing for HTML forms.
        if ($method === 'POST' && isset($this->body['_method'])) {
            $method = strtoupper((string) $this->body['_method']);
        }
        return $method;
    }

    public function uri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return '/' . trim(rawurldecode($uri), '/');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Retrieve a trimmed string input.
     */
    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return trim((string) $value);
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->input($key, $default);
    }

    public function bool(string $key): bool
    {
        return filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key): ?string
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$normalized] ?? null;
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest';
    }

    public function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return $this->isAjax() || str_contains($accept, 'application/json');
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}
