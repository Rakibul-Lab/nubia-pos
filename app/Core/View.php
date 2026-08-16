<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Lightweight PHP template renderer with layout support.
 *
 * @package App\Core
 */
final class View
{
    /** @var array<string,mixed> Data shared with every view. */
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Render a view within the main app layout.
     *
     * @param array<string,mixed> $data
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        $file = config('paths.views') . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$view} ({$file})");
        }

        $scope = array_merge(self::$shared, $data);
        extract($scope, EXTR_SKIP);

        ob_start();
        include $file;
        $content = (string) ob_get_clean();

        // Views may define $pageScript (and other layout vars) during render.
        $layoutData = array_merge($data, ['content' => $content]);
        if (isset($pageScript) && $pageScript !== '') {
            $layoutData['pageScript'] = $pageScript;
        }

        return self::partial($layout, $layoutData);
    }

    /**
     * Render a view file without a layout and return the output.
     *
     * @param array<string,mixed> $data
     */
    public static function partial(string $view, array $data = []): string
    {
        $file = config('paths.views') . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$view} ({$file})");
        }

        extract(array_merge(self::$shared, $data), EXTR_SKIP);

        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    /**
     * Echo a rendered view (with layout) to the output buffer.
     *
     * @param array<string,mixed> $data
     */
    public static function display(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        echo self::render($view, $data, $layout);
    }

    public static function renderError(int $status, string $message): void
    {
        $file = config('paths.views') . '/errors/generic.php';
        if (is_file($file)) {
            extract(['status' => $status, 'message' => $message]);
            include $file;
            return;
        }
        echo "<h1>{$status}</h1><p>" . e($message) . '</p>';
    }
}
