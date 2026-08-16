<?php
use App\Core\View;
/** @var float $revenue @var float $cogs @var float $grossProfit @var float $expenses @var float $directProfit @var float $netProfit @var string $start @var string $end */
?>
<?= View::partial('components.page_head', ['title' => 'Profit & Loss', 'subtitle' => date('M j, Y', strtotime($start)) . ' — ' . date('M j, Y', strtotime($end))]) ?>
<?= View::partial('accounting.nav') ?>
<form method="GET" class="card mb-3"><div class="card-body d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label">From</label><input type="date" name="start" class="form-control" value="<?= e($start) ?>"></div>
    <div><label class="form-label">To</label><input type="date" name="end" class="form-control" value="<?= e($end) ?>"></div>
    <button class="btn btn-brand"><span class="material-symbols-rounded">filter_alt</span> Generate</button>
</div></form>

<div class="row g-3 justify-content-center"><div class="col-lg-7">
    <div class="card"><div class="card-body">
        <?php
        $line = static function (string $label, float $val, bool $bold = false, ?string $color = null) {
            $style = $color ? "color:$color;" : '';
            $w = $bold ? 'fw-800' : '';
            echo '<div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border);"><span class="' . $w . '">' . e($label) . '</span><span class="' . $w . '" style="' . $style . '">' . money($val) . '</span></div>';
        };
        if (can('sales_total.view')) {
            $line('Sales Total', $revenue);
        }
        if (can('costs.view')) {
            $line('Cost of Goods Sold', -$cogs);
        }
        if (can('profit.view')) {
            $line('Gross Profit', $grossProfit, true, 'var(--brand)');
        }
        if (can('expenses.view')) {
            $line('Operating Expenses', -$expenses);
        }
        if (can('profit.view')) {
            $line('Direct Buy & Sell Profit', $directProfit);
        }
        ?>
        <?php if (can('profit.view')): ?>
        <div class="d-flex justify-content-between py-3 mt-2" style="border-top:2px solid var(--brand);">
            <span class="fw-800" style="font-size:1.1rem;">Net Profit</span>
            <span class="fw-800" style="font-size:1.3rem;color:<?= $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' ?>;"><?= money($netProfit) ?></span>
        </div>
        <?php endif; ?>
    </div></div>
</div></div>
