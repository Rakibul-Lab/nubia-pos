<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Single source of truth for assignable permissions and route policy.
 */
final class PermissionRegistry
{
    /** @var array<string,mixed>|null */
    private static ?array $config = null;

    /**
     * @return array<string,array<string,string>>
     */
    public static function catalog(): array
    {
        return self::config()['catalog'] ?? [];
    }

    /**
     * @return string[]
     */
    public static function slugs(): array
    {
        $slugs = [];
        foreach (self::catalog() as $permissions) {
            $slugs = array_merge($slugs, array_keys($permissions));
        }
        return $slugs;
    }

    public static function forHandler(string $handler): ?string
    {
        return self::config()['handlers'][$handler] ?? null;
    }

    public static function isSelfService(string $handler): bool
    {
        return in_array($handler, self::config()['self_service_handlers'] ?? [], true);
    }

    /**
     * Assert that every authenticated controller handler is explicitly covered.
     */
    public static function authorizeHandler(string $handler, Request $request): void
    {
        $permission = self::forHandler($handler);
        if ($permission !== null) {
            Middleware::run('permission:' . $permission, $request);
            return;
        }

        if (!self::isSelfService($handler)) {
            if ($request->wantsJson()) {
                Response::error('No access policy is configured for this feature.', [], 403);
            }
            Response::abort(403);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require BASE_PATH . '/config/permissions.php';
        }
        return self::$config;
    }
}
