<?php
use App\Core\View;
/** @var array $transactions @var array $meta @var array $stats */
$canCosts = can('costs.view');
$canProfit = can('profit.view');
$canRevenue = can('revenue.view');
$colspan = 6 + ($canCosts ? 1 : 0) + ($canRevenue ? 1 : 0) + ($canProfit ? 1 : 0);
?>
<?= View::partial('components.page_head', [
    'title'       => 'Direct Buy & Sell',
    'subtitle'    => 'Instant buy-and-sell transactions for out-of-stock demand',
    'actionUrl'   => can('direct.create') ? url('direct-buy-sell/create') : null,
    'actionLabel' => 'New Transaction',
    'actionIcon'  => 'bolt',
]) ?>

<div class="row g-3 mb-1">
    <div class="col-md-4"><div class="stat-card"><div class="stat-icon bg-grad-1"><span class="material-symbols-rounded">bolt</span></div><div class="stat-label">Total Transactions</div><div class="stat-value"><?= number_format((int) $stats['cnt']) ?></div></div></div>
    <?php if (can('revenue.view')): ?>
    <div class="col-md-4"><div class="stat-card"><div class="stat-icon bg-grad-4"><span class="material-symbols-rounded">payments</span></div><div class="stat-label">Total Revenue</div><div class="stat-value"><?= money($stats['revenue']) ?></div></div></div>
    <?php endif; ?>
    <?php if ($canProfit): ?>
    <div class="col-md-4"><div class="stat-card"><div class="stat-icon bg-grad-2"><span class="material-symbols-rounded">trending_up</span></div><div class="stat-label">Total Profit</div><div class="stat-value"><?= money($stats['profit']) ?></div></div></div>
    <?php endif; ?>
</div>

<div class="card mt-3">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Reference</th><th>Product</th><th>Customer</th><th>Qty</th><?php if ($canCosts): ?><th>Cost</th><?php endif; ?><?php if (can('revenue.view')): ?><th>Revenue</th><?php endif; ?><?php if ($canProfit): ?><th>Profit</th><?php endif; ?><th>Inventory</th><th class="text-end"></th></tr></thead>
            <tbody>
            <?php if (!$transactions): ?><tr><td colspan="<?= $colspan ?>"><?= View::partial('components.empty', ['icon' => 'bolt', 'title' => 'No direct transactions', 'text' => 'Create one when a customer wants an out-of-stock item.']) ?></td></tr><?php endif; ?>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td class="fw-800"><?= e($t['reference']) ?></td>
                    <td><?= e($t['product_name']) ?></td>
                    <td><?= e($t['customer_name'] ?? 'Walk-in') ?></td>
                    <td><?= rtrim(rtrim(number_format((float) $t['quantity'], 2), '0'), '.') ?></td>
                    <?php if ($canCosts): ?><td><?= money($t['total_cost']) ?></td><?php endif; ?>
                    <?php if (can('revenue.view')): ?><td><?= money((float) $t['selling_price'] * (float) $t['quantity']) ?></td><?php endif; ?>
                    <?php if ($canProfit): ?><td><span class="badge-pill <?= (float) $t['profit'] >= 0 ? 'badge-success' : 'badge-danger' ?>"><?= money($t['profit']) ?></span></td><?php endif; ?>
                    <td><span class="badge-pill <?= $t['add_to_inventory'] ? 'badge-info' : 'badge-muted' ?>"><?= $t['add_to_inventory'] ? 'Added' : 'Not added' ?></span></td>
                    <td class="text-end"><a href="<?= url('direct-buy-sell/' . $t['id']) ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('direct-buy-sell')]) ?></div>
</div>
