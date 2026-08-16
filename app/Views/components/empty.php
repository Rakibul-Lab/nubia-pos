<?php
/** @var string|null $icon @var string|null $title @var string|null $text */
?>
<div class="empty-state">
    <span class="material-symbols-rounded"><?= e($icon ?? 'inbox') ?></span>
    <h5 class="mt-2 mb-1 fw-800"><?= e($title ?? 'Nothing here yet') ?></h5>
    <p class="mb-0"><?= e($text ?? 'Records you create will appear here.') ?></p>
</div>
