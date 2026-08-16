<?php

/**
 * Dynamic web app manifest (uses business name from settings when available).
 *
 * @package App\Controllers
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PwaController extends Controller
{
    public function manifest(): void
    {
        $name = setting('business_name', config('app.name', 'Nubia Inventory'));
        $short = mb_strlen($name) > 12 ? 'Nubia' : $name;

        $manifest = [
            'id' => '/',
            'name' => $name,
            'short_name' => $short,
            'description' => 'Inventory, POS, sales, purchases and accounting — built for real retail.',
            'lang' => setting('language', 'en'),
            'dir' => 'ltr',
            'start_url' => '/login',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui', 'browser'],
            'orientation' => 'any',
            'background_color' => '#0a0a0a',
            'theme_color' => '#dc2626',
            'categories' => ['business', 'finance', 'productivity'],
            'prefer_related_applications' => false,
            'icons' => [
                ['src' => '/assets/icons/icon-72.png', 'sizes' => '72x72', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-96.png', 'sizes' => '96x96', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-128.png', 'sizes' => '128x128', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-144.png', 'sizes' => '144x144', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-152.png', 'sizes' => '152x152', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-256.png', 'sizes' => '256x256', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-384.png', 'sizes' => '384x384', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/assets/icons/maskable-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/assets/icons/maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                [
                    'name' => 'Open Nubia',
                    'short_name' => 'Home',
                    'url' => '/login',
                    'icons' => [['src' => '/assets/icons/icon-192.png', 'sizes' => '192x192']],
                ],
            ],
            'handle_links' => 'preferred',
            'launch_handler' => ['client_mode' => ['navigate-existing', 'auto']],
        ];

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function browserconfig(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo '<?xml version="1.0" encoding="utf-8"?>'
            . '<browserconfig><msapplication><tile>'
            . '<square150x150logo src="/assets/icons/icon-192.png"/>'
            . '<TileColor>#dc2626</TileColor>'
            . '</tile></msapplication></browserconfig>';
        exit;
    }
}
