<?php
/** @var string $title */
$appName = setting('business_name', config('app.name'));
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="application-name" content="<?= e($appName) ?>">
<meta name="description" content="Inventory, POS, sales, purchases and accounting — Nubia Inventory.">
<meta name="theme-color" content="#dc2626" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0a0a0a" media="(prefers-color-scheme: dark)">
<meta name="color-scheme" content="light dark">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="<?= e(mb_strlen($appName) > 12 ? 'Nubia' : $appName) ?>">
<meta name="format-detection" content="telephone=no">
<meta name="msapplication-TileColor" content="#dc2626">
<meta name="msapplication-config" content="<?= url('browserconfig.xml') ?>">

<title><?= e($title ?? 'Dashboard') ?> · <?= e($appName) ?></title>

<link rel="manifest" href="<?= url('manifest.webmanifest') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset('icons/favicon-32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= asset('icons/favicon-16.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= asset('icons/apple-touch-icon.png') ?>">
<link rel="mask-icon" href="<?= asset('icons/icon-192.png') ?>" color="#dc2626">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="<?= asset_v('css/app.css') ?>" rel="stylesheet">

<script>
    // Apply theme + sidebar preference before paint (avoids expand→collapse flash).
    (function () {
        var t = localStorage.getItem('nubia-theme') || '<?= e(setting('theme', 'light')) ?>';
        document.documentElement.setAttribute('data-theme', t);
        if (localStorage.getItem('nubia-sidebar-collapsed') === '1') {
            document.documentElement.classList.add('sidebar-collapsed-pref', 'sidebar-boot');
        }
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            document.documentElement.classList.add('is-pwa');
        }
    })();
    window.NUBIA_BASE = '<?= e(app_base_url()) ?>';
    window.NUBIA_VERSION = '<?= e(config('app.version')) ?>';
    window.CSRF_TOKEN = '<?= e(csrf_token()) ?>';
    window.CURRENCY_SYMBOL = '<?= e(setting('currency_symbol', 'Tk')) ?>';
</script>
