<?php
$uri = (new \App\Core\Request())->uri();
$isActive = static fn (string $p): string => str_starts_with($uri, $p) ? 'active' : '';
?>
<nav class="bottom-nav d-lg-none">
    <?php if (can('dashboard.view')): ?>
        <a href="<?= url('dashboard') ?>" class="<?= $isActive('/dashboard') ?>">
            <span class="material-symbols-rounded">dashboard</span> Home
        </a>
    <?php endif; ?>
    <?php if (can('products.view')): ?>
        <a href="<?= url('products') ?>" class="<?= $isActive('/products') ?>">
            <span class="material-symbols-rounded">inventory_2</span> Products
        </a>
    <?php endif; ?>
    <?php if (can('pos.use')): ?>
        <a href="<?= url('pos') ?>" class="fab" style="display:grid;place-items:center;">
            <span class="material-symbols-rounded">point_of_sale</span>
        </a>
    <?php endif; ?>
    <?php if (can('sales.view')): ?>
        <a href="<?= url('sales') ?>" class="<?= $isActive('/sales') ?>">
            <span class="material-symbols-rounded">shopping_cart</span> Sales
        </a>
    <?php endif; ?>
    <a href="#" id="mobileMoreBtn" onclick="Nubia.sidebar.toggle();return false;">
        <span class="material-symbols-rounded">menu</span> More
    </a>
</nav>
