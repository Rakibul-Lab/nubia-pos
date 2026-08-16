<?php

$requestPath = (new \App\Core\Request())->uri();
$accountingLinks = [
    ['accounting.cash_book', 'Cash Book', 'payments', '/accounting/cash-book'],
    ['accounting.bank_book', 'Bank Book', 'account_balance', '/accounting/bank-book'],
    ['accounting.profit_loss', 'Profit & Loss', 'trending_up', '/accounting/profit-loss'],
    ['accounting.payables', 'Payables', 'outbox', '/accounting/payables'],
    ['accounting.receivables', 'Receivables', 'move_to_inbox', '/accounting/receivables'],
];
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($accountingLinks as [$permission, $label, $icon, $path]): ?>
        <?php if (!can($permission)) continue; ?>
        <a href="<?= url($path) ?>" class="btn <?= $requestPath === $path ? 'btn-brand' : 'btn-soft' ?> btn-sm">
            <span class="material-symbols-rounded"><?= e($icon) ?></span> <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</div>
