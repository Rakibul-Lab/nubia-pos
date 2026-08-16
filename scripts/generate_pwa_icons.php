<?php

declare(strict_types=1);

$dir = dirname(__DIR__) . '/public/assets/icons';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

function roundedRect($im, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
{
    imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
    imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
    imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

function drawN($im, int $pad, int $size, int $white): void
{
    $s = $size - 2 * $pad;
    $w = $s * 0.42;
    $h = $s * 0.52;
    $lx = $pad + ($s - $w) / 2;
    $ty = $pad + ($s - $h) / 2;
    $stroke = max(2, (int) round($s * 0.11));

    imagefilledrectangle($im, (int) $lx, (int) $ty, (int) ($lx + $stroke), (int) ($ty + $h), $white);
    imagefilledrectangle($im, (int) ($lx + $w - $stroke), (int) $ty, (int) ($lx + $w), (int) ($ty + $h), $white);
    $points = [
        (int) ($lx + $stroke * 0.2), (int) $ty,
        (int) ($lx + $stroke * 1.35), (int) $ty,
        (int) ($lx + $w - $stroke * 0.2), (int) ($ty + $h),
        (int) ($lx + $w - $stroke * 1.35), (int) ($ty + $h),
    ];
    imagefilledpolygon($im, $points, $white);
}

$sizes = [72, 96, 128, 144, 152, 180, 192, 256, 384, 512];
foreach ($sizes as $size) {
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $pad = (int) round($size * 0.08);
    $radius = (int) round(($size - 2 * $pad) * 0.22);
    $red = imagecolorallocate($im, 220, 38, 38);
    $white = imagecolorallocate($im, 255, 255, 255);

    roundedRect($im, $pad, $pad, $size - $pad - 1, $size - $pad - 1, $radius, $red);
    drawN($im, $pad, $size, $white);

    $path = $dir . "/icon-{$size}.png";
    imagepng($im, $path);
    imagedestroy($im);
    echo "wrote {$path}\n";
}

foreach ([192, 512] as $size) {
    $im = imagecreatetruecolor($size, $size);
    $bg = imagecolorallocate($im, 10, 10, 10);
    imagefill($im, 0, 0, $bg);

    $pad = (int) round($size * 0.18);
    $radius = (int) round(($size - 2 * $pad) * 0.22);
    $red = imagecolorallocate($im, 220, 38, 38);
    $white = imagecolorallocate($im, 255, 255, 255);

    roundedRect($im, $pad, $pad, $size - $pad - 1, $size - $pad - 1, $radius, $red);
    drawN($im, $pad, $size, $white);

    $path = $dir . "/maskable-{$size}.png";
    imagepng($im, $path);
    imagedestroy($im);
    echo "wrote {$path}\n";
}

copy($dir . '/icon-180.png', $dir . '/apple-touch-icon.png');

foreach ([16, 32] as $size) {
    $src = imagecreatefrompng($dir . '/icon-192.png');
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);
    imagecopyresampled($im, $src, 0, 0, 0, 0, $size, $size, 192, 192);
    imagepng($im, $dir . "/favicon-{$size}.png");
    imagedestroy($im);
    imagedestroy($src);
    echo "wrote favicon-{$size}.png\n";
}

echo "done\n";
