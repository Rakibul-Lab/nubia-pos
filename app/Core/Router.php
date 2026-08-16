<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple, fast HTTP router with named parameters and middleware groups.
 *
 * @package App\Core
 */
final class Router
{
    /** @var array<string,array<int,array{pattern:string,handler:mixed,middleware:string[]}>> */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    /** @var string[] */
    private array $groupMiddleware = [];

    private string $groupPrefix = '';

    public function get(string $path, mixed $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): self
    {
        return $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, mixed $handler): self
    {
        return $this->add('DELETE', $path, $handler);
    }

    /**
     * Group routes under a shared prefix and middleware stack.
     *
     * @param string[] $middleware
     */
    public function group(array $middleware, callable $callback, string $prefix = ''): void
    {
        $previousMw     = $this->groupMiddleware;
        $previousPrefix = $this->groupPrefix;

        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $this->groupPrefix    .= $prefix;

        $callback($this);

        $this->groupMiddleware = $previousMw;
        $this->groupPrefix     = $previousPrefix;
    }

    private function add(string $method, string $path, mixed $handler): self
    {
        $fullPath = $this->groupPrefix . $path;
        $pattern  = $this->compile($fullPath);

        $this->routes[$method][] = [
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $this->groupMiddleware,
        ];

        return $this;
    }

    private function compile(string $path): string
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            return '#^/$#';
        }
        // Convert {id} into a named capture group.
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    /**
     * Dispatch the current request.
     */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        if (!isset($this->routes[$method])) {
            Response::abort(405, 'Method Not Allowed');
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware.
                foreach ($route['middleware'] as $mw) {
                    Middleware::run($mw, $request);
                }

                // Authenticated application features are fail-closed: every
                // controller action must be in the central permission policy
                // or explicitly marked as signed-in self service.
                if (is_string($route['handler']) && in_array('auth', $route['middleware'], true)) {
                    PermissionRegistry::authorizeHandler($route['handler'], $request);
                }

                $this->invoke($route['handler'], $params, $request);
                return;
            }
        }

        Response::abort(404);
    }

    /**
     * @param array<string,string> $params
     */
    private function invoke(mixed $handler, array $params, Request $request): void
    {
        if (is_callable($handler)) {
            $handler($request, ...array_values($params));
            return;
        }

        // "Controller@method" string form.
        [$class, $method] = explode('@', (string) $handler);
        $fqcn = 'App\\Controllers\\' . $class;

        if (!class_exists($fqcn)) {
            Response::abort(500, "Controller not found: {$fqcn}");
        }

        $controller = new $fqcn();
        if (!method_exists($controller, $method)) {
            Response::abort(500, "Method not found: {$fqcn}@{$method}");
        }

        $controller->{$method}(...array_values($params));
    }
}
