<?php
use App\Services\NotificationService;

$user = auth();
$initials = strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
$notif = NotificationService::forCurrentUser();
$notifItems = $notif['items'];
$notifUnread = (int) $notif['unread'];
$canSearch = can('products.view')
    || can('products.serials.view')
    || can('sales.view')
    || can('customers.view')
    || can('suppliers.view');

$iconClass = static function (string $type): string {
    return match ($type) {
        'low_stock'   => 'text-warning',
        'receivables' => 'text-info',
        'danger'      => 'text-danger',
        'success'     => 'text-success',
        default       => 'text-muted-2',
    };
};
?>
<header class="topbar">
    <button class="icon-btn d-lg-none" id="mobileMenuBtn" aria-label="Menu">
        <span class="material-symbols-rounded">menu</span>
    </button>
    <button class="icon-btn d-none d-lg-grid" id="sidebarToggle" aria-label="Toggle sidebar">
        <span class="material-symbols-rounded">menu_open</span>
    </button>

    <?php if ($canSearch): ?>
        <div class="global-search">
            <span class="material-symbols-rounded">search</span>
            <input type="text" id="globalSearch" placeholder="Search permitted records…" autocomplete="off">
            <div class="search-results" id="searchResults"></div>
        </div>
    <?php endif; ?>

    <div class="ms-auto d-flex align-items-center gap-2">
        <?php if (can('pos.use')): ?>
            <a href="<?= url('pos') ?>" class="btn btn-brand d-none d-md-inline-flex">
                <span class="material-symbols-rounded">add</span> New Sale
            </a>
        <?php endif; ?>

        <button class="icon-btn d-none" id="pwaInstallBtn" aria-label="Install app" title="Install Nubia">
            <span class="material-symbols-rounded">install_desktop</span>
        </button>

        <button class="icon-btn" id="themeToggle" aria-label="Toggle theme">
            <span class="material-symbols-rounded">dark_mode</span>
        </button>

        <div class="dropdown topbar-dropdown">
            <button class="icon-btn" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications">
                <span class="material-symbols-rounded">notifications</span>
                <?php if ($notifUnread > 0): ?><span class="dot"></span><?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end topbar-menu">
                <div class="topbar-menu-title d-flex align-items-center justify-content-between gap-2">
                    <span>Notifications</span>
                    <?php if ($notifUnread > 0): ?>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="notifMarkAll" style="font-size:.75rem;font-weight:700;">Clear all</button>
                    <?php endif; ?>
                </div>
                <?php if ($notifItems === []): ?>
                    <div class="px-3 py-3 text-muted-2" style="font-size:.85rem;">You're all caught up.</div>
                <?php else: ?>
                    <?php foreach ($notifItems as $n): ?>
                        <a class="dropdown-item notif-item" href="<?= url('notifications/' . (int) $n['id'] . '/open') ?>">
                            <span class="material-symbols-rounded <?= e($iconClass((string) $n['type'])) ?>"><?= e($n['icon'] ?: 'notifications') ?></span>
                            <span>
                                <span class="d-block"><?= e($n['title']) ?></span>
                                <?php if (!empty($n['body'])): ?>
                                    <span class="d-block text-muted-2" style="font-size:.75rem;font-weight:500;"><?= e($n['body']) ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="dropdown topbar-dropdown">
            <button type="button" class="avatar topbar-avatar" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="Account menu"><?= e($initials) ?></button>
            <div class="dropdown-menu dropdown-menu-end topbar-menu topbar-menu-user">
                <div class="px-2 py-1">
                    <div class="fw-800"><?= e($user['name'] ?? 'User') ?></div>
                    <div class="text-muted-2" style="font-size:.8rem;"><?= e($user['role_name'] ?? '') ?></div>
                </div>
                <hr class="dropdown-divider">
                <a class="dropdown-item" href="<?= url('profile') ?>"><span class="material-symbols-rounded">person</span> My Profile</a>
                <?php if (can('settings.view')): ?>
                    <a class="dropdown-item" href="<?= url('settings') ?>"><span class="material-symbols-rounded">settings</span> Settings</a>
                <?php endif; ?>
                <hr class="dropdown-divider">
                <form method="POST" action="<?= url('logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="dropdown-item text-danger w-100"><span class="material-symbols-rounded">logout</span> Sign Out</button>
                </form>
            </div>
        </div>
    </div>
</header>
