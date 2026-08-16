<?php
/**
 * Reusable page header with title, subtitle and optional action button.
 * @var string $title
 * @var string|null $subtitle
 * @var string|null $actionUrl
 * @var string|null $actionLabel
 * @var string|null $actionIcon
 */
?>
<div class="page-head animate-in">
    <div>
        <h1 class="page-title"><?= e($title ?? '') ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p class="page-sub"><?= e($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <?php if (!empty($extraActions)): ?><?= $extraActions ?><?php endif; ?>
        <?php if (!empty($actionUrl)): ?>
            <a href="<?= e($actionUrl) ?>" class="btn btn-brand">
                <span class="material-symbols-rounded"><?= e($actionIcon ?? 'add') ?></span>
                <?= e($actionLabel ?? 'Create') ?>
            </a>
        <?php endif; ?>
    </div>
</div>
