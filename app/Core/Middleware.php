<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Named middleware runner.
 *
 * @package App\Core
 */
final class Middleware
{
    public static function run(string $name, Request $request): void
    {
        // Support "permission:products.view" style parameters.
        [$name, $param] = array_pad(explode(':', $name, 2), 2, null);

        match ($name) {
            'auth'       => self::auth($request),
            'guest'      => self::guest(),
            'csrf'       => self::csrf($request),
            'permission' => self::permission($request, (string) $param),
            'role'       => self::role($request, (string) $param),
            default      => null,
        };
    }

    private static function auth(Request $request): void
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                Response::error('Unauthenticated.', [], 401);
            }
            flash('error', 'Please sign in to continue.');
            redirect('login');
        }
    }

    private static function guest(): void
    {
        if (Auth::check()) {
            redirect(Auth::landingPath());
        }
    }

    private static function csrf(Request $request): void
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Csrf::check($request);
        }
    }

    private static function permission(Request $request, string $permission): void
    {
        if (!Auth::can($permission)) {
            if ($request->wantsJson()) {
                Response::error('You are not authorized to perform this action.', [], 403);
            }
            Response::abort(403);
        }
    }

    private static function role(Request $request, string $roles): void
    {
        $allowed = explode(',', $roles);
        if (!Auth::hasRole(...$allowed)) {
            if ($request->wantsJson()) {
                Response::error('Forbidden.', [], 403);
            }
            Response::abort(403);
        }
    }
}
