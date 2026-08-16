<?php
/**
 * Reusable pagination component.
 * @var array{total:int,page:int,per_page:int,last_page:int} $meta
 * @var string $baseUrl
 * @var string $pageParam Query string key for the page number (default: page)
 */
$meta      = $meta ?? ['total' => 0, 'page' => 1, 'per_page' => 15, 'last_page' => 1];
$baseUrl   = $baseUrl ?? '';
$pageParam = $pageParam ?? 'page';
$sep       = str_contains($baseUrl, '?') ? '&' : '?';
if (($meta['last_page'] ?? 1) <= 1) {
    return;
}
$page = (int) $meta['page'];
$last = (int) $meta['last_page'];
$from = ($page - 1) * $meta['per_page'] + 1;
$to   = min($page * $meta['per_page'], $meta['total']);
$link = static fn (int $p): string => $baseUrl . $sep . rawurlencode($pageParam) . '=' . $p;

$start = max(1, $page - 2);
$end   = min($last, $page + 2);
?>
<nav class="nubia-pager" aria-label="Pagination">
    <div class="nubia-pager-meta">
        <span class="nubia-pager-chip">
            <span class="material-symbols-rounded">stacks</span>
            <?= $from ?>–<?= $to ?> of <?= number_format($meta['total']) ?>
        </span>
        <span class="d-none d-sm-inline">Page <?= $page ?> of <?= $last ?></span>
    </div>

    <ul class="nubia-pager-list">
        <li>
            <a class="nubia-pager-link nubia-pager-edge <?= $page <= 1 ? 'is-disabled' : '' ?>"
               href="<?= e($link(max(1, $page - 1))) ?>" aria-label="Previous page">
                <span class="material-symbols-rounded">chevron_left</span>
            </a>
        </li>

        <?php if ($start > 1): ?>
            <li><a class="nubia-pager-link" href="<?= e($link(1)) ?>">1</a></li>
            <?php if ($start > 2): ?>
                <li><span class="nubia-pager-gap">…</span></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li>
                <a class="nubia-pager-link <?= $i === $page ? 'is-active' : '' ?>"
                   href="<?= e($link($i)) ?>"
                   <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($end < $last): ?>
            <?php if ($end < $last - 1): ?>
                <li><span class="nubia-pager-gap">…</span></li>
            <?php endif; ?>
            <li><a class="nubia-pager-link" href="<?= e($link($last)) ?>"><?= $last ?></a></li>
        <?php endif; ?>

        <li>
            <a class="nubia-pager-link nubia-pager-edge <?= $page >= $last ? 'is-disabled' : '' ?>"
               href="<?= e($link(min($last, $page + 1))) ?>" aria-label="Next page">
                <span class="material-symbols-rounded">chevron_right</span>
            </a>
        </li>
    </ul>
</nav>
