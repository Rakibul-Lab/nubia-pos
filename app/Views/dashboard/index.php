<?php
use App\Core\View;
/** @var array $kpis @var array $topProducts @var array $recentSales @var array $lowStock @var array $bestCustomers @var array $trend @var array $payments @var string $period @var string $start @var string $end */

$period = $period ?? 'today';
$start  = $start ?? date('Y-m-d');
$end    = $end ?? date('Y-m-d');

$periodLabels = [
    'today'     => 'Today',
    'yesterday' => 'Yesterday',
    'week'      => 'This Week',
    'month'     => 'This Month',
    'year'      => 'This Year',
    'custom'    => 'Custom Range',
];

$subtitle = ($periodLabels[$period] ?? 'Period') . ' · ' . date('M j, Y', strtotime($start));
if ($start !== $end) {
    $subtitle .= ' – ' . date('M j, Y', strtotime($end));
}

$canDashSales     = can('dashboard.sales');
$canDashProfit    = can('dashboard.profit');
$canDashPurchase  = can('dashboard.purchase');
$canDashExpenses  = can('dashboard.expenses');
$canDashRevenue   = can('dashboard.revenue');
$canDashDueIn     = can('dashboard.due_collection');
$canDashDueOut    = can('dashboard.due_payment');
$canDashStockVal  = can('dashboard.stock_value');
$canDashKpis      = can('dashboard.metrics')
    || $canDashSales || $canDashProfit || $canDashPurchase || $canDashExpenses
    || $canDashRevenue || $canDashDueIn || $canDashDueOut || $canDashStockVal;

$cards = [];
if ($canDashKpis) {
    if ($canDashSales) {
        $cards[] = ['Sales', money($kpis['sales'] ?? 0), 'point_of_sale', 'bg-grad-1'];
    }
    if ($canDashProfit) {
        $cards[] = ['Profit', money($kpis['profit'] ?? 0), 'trending_up', 'bg-grad-2'];
    }
    if ($canDashPurchase) {
        $cards[] = ['Purchase', money($kpis['purchase'] ?? 0), 'local_shipping', 'bg-grad-4'];
    }
    if ($canDashExpenses) {
        $cards[] = ['Expenses', money($kpis['expense'] ?? 0), 'payments', 'bg-grad-3'];
    }
    if ($canDashRevenue) {
        $cards[] = ['Revenue', money($kpis['revenue'] ?? 0), 'account_balance_wallet', 'bg-grad-6'];
    }
    if ($canDashDueIn) {
        $cards[] = ['Due Collection', money($kpis['due_collect'] ?? 0), 'call_received', 'bg-grad-2'];
    }
    if ($canDashDueOut) {
        $cards[] = ['Due Payment', money($kpis['due_pay'] ?? 0), 'call_made', 'bg-grad-5'];
    }
    if ($canDashStockVal) {
        $cards[] = ['Stock Value', money($kpis['stock_value'] ?? 0), 'inventory_2', 'bg-grad-1'];
    }
}
?>

<?= View::partial('components.page_head', [
    'title'    => 'Dashboard',
    'subtitle' => 'Welcome back, ' . e(auth()['name'] ?? '') . ' · ' . $subtitle,
]) ?>

<div class="card mb-3 report-filters"><div class="card-body py-3">
    <form method="GET" id="dashboardFilterForm" action="<?= url('dashboard') ?>" class="row g-2 align-items-end">
        <div class="col-md-4 col-lg-3">
            <label class="form-label" for="dashPeriod">Date Range</label>
            <select name="period" id="dashPeriod" class="form-select">
                <?php foreach ($periodLabels as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $period === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2 col-lg-2 report-custom-dates" id="dashCustomStart" style="<?= $period === 'custom' ? '' : 'display:none;' ?>">
            <label class="form-label" for="dashStart">From</label>
            <input type="date" name="start" id="dashStart" class="form-control" value="<?= e($start) ?>">
        </div>
        <div class="col-6 col-md-2 col-lg-2 report-custom-dates" id="dashCustomEnd" style="<?= $period === 'custom' ? '' : 'display:none;' ?>">
            <label class="form-label" for="dashEnd">To</label>
            <input type="date" name="end" id="dashEnd" class="form-control" value="<?= e($end) ?>">
        </div>
        <div class="col-auto<?= $period === 'custom' ? ' d-grid' : ' d-none' ?>" id="dashApplyWrap">
            <button type="submit" class="btn btn-brand"><span class="material-symbols-rounded">filter_alt</span> Apply</button>
        </div>
    </form>
</div></div>

<!-- KPI cards -->
<?php if ($canDashKpis): ?>
<?php if ($cards): ?>
<div class="row g-3 mb-1">
    <?php foreach ($cards as $i => $c): ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="stat-card animate-in d<?= ($i % 4) + 1 ?>">
                <div class="stat-icon <?= $c[3] ?>"><span class="material-symbols-rounded"><?= $c[2] ?></span></div>
                <div class="stat-label"><?= e($c[0]) ?></div>
                <div class="stat-value"><?= $c[1] ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Secondary metrics (live snapshot) -->
<div class="row g-3 my-1">
    <?php
    $secondary = [];
    if (can('products.view')) {
        $secondary[] = ['Total Products', number_format((int) ($kpis['total_products'] ?? 0)), 'inventory'];
    }
    if (can('customers.view')) {
        $secondary[] = ['Total Customers', number_format((int) ($kpis['total_customers'] ?? 0)), 'groups'];
    }
    if (can('suppliers.view')) {
        $secondary[] = ['Total Suppliers', number_format((int) ($kpis['total_suppliers'] ?? 0)), 'diversity_3'];
    }
    if ($canDashDueIn || $canDashDueOut) {
        $secondary[] = ['Receivable / Payable', money($kpis['receivable'] ?? 0, false) . ' / ' . money($kpis['payable'] ?? 0, false), 'swap_horiz'];
    }
    foreach ($secondary as $s): ?>
        <div class="col-6 col-md-3">
            <div class="glass p-3 h-100">
                <div class="d-flex align-items-center gap-2 text-muted-2 mb-1" style="font-size:.78rem;font-weight:600;">
                    <span class="material-symbols-rounded" style="font-size:19px;"><?= $s[2] ?></span><?= e($s[0]) ?>
                </div>
                <div class="fw-800" style="font-size:1.05rem;"><?= $s[1] ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Charts -->
<?php if (can('dashboard.charts')): ?>
<div class="row g-3 my-1">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <span><?= $canDashSales ? 'Sales' : 'Trend' ?><?= $canDashPurchase ? ($canDashSales ? ', Purchase' : 'Purchase') : '' ?><?= $canDashProfit ? ' &amp; Profit' : '' ?></span>
                <span class="text-muted-2" style="font-size:.8rem;font-weight:600;"><?= e($periodLabels[$period] ?? '') ?></span>
            </div>
            <div class="card-body"><canvas id="salesChart" height="110"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Payment Methods</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="paymentChart" height="230"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Lists -->
<?php if (can('sales.view') || can('stock.view')): ?>
<div class="row g-3 my-1">
    <?php if (can('sales.view')): ?><div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><span>Recent Sales</span><?php if (can('sales.view')): ?><a href="<?= url('sales') ?>" class="btn btn-soft btn-sm">View all</a><?php endif; ?></div>
            <div class="table-wrap">
                <table class="nubia">
                    <thead><tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php if (!$recentSales): ?>
                        <tr><td colspan="5"><?= View::partial('components.empty', ['icon' => 'receipt_long', 'title' => 'No sales in this period', 'text' => 'Try another date range or start selling from the POS.']) ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><strong><?= e($sale['invoice_no']) ?></strong><div class="text-muted-2" style="font-size:.75rem;"><?= date('M j, Y', strtotime($sale['sale_date'])) ?></div></td>
                            <td><?= e($sale['customer']) ?></td>
                            <td class="fw-800"><?= money($sale['total']) ?></td>
                            <td>
                                <?php $pm = $sale['payment_status']; ?>
                                <span class="badge-pill <?= $pm === 'paid' ? 'badge-success' : ($pm === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($pm) ?></span>
                            </td>
                            <td><?php if (can('sales.view')): ?><a href="<?= url('sales/' . $sale['id']) ?>" class="icon-btn" style="width:34px;height:34px;"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><?php endif; ?>
    <div class="<?= can('sales.view') ? 'col-lg-5' : 'col-12' ?>">
        <?php if (can('sales.view')): ?><div class="card mb-3">
            <div class="card-header"><span>Top Selling Products</span></div>
            <div class="card-body py-2">
                <?php if (!$topProducts): ?><p class="text-muted-2 mb-0 py-3 text-center">No sales data for this period.</p><?php endif; ?>
                <?php foreach ($topProducts as $p): ?>
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:var(--border)!important;">
                        <div class="avatar sm"><?= e(strtoupper(substr($p['name'], 0, 1))) ?></div>
                        <div class="flex-grow-1">
                            <div class="fw-800" style="font-size:.9rem;"><?= e($p['name']) ?></div>
                            <div class="text-muted-2" style="font-size:.75rem;"><?= (int) $p['qty'] ?> sold</div>
                        </div>
                        <?php if ($canDashRevenue): ?><div class="fw-800" style="font-size:.85rem;"><?= money($p['revenue'] ?? 0) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div><?php endif; ?>
        <?php if (can('stock.view')): ?><div class="card">
            <div class="card-header"><span class="text-danger d-flex align-items-center gap-1"><span class="material-symbols-rounded" style="font-size:19px;">warning</span> Low Stock Alert</span><?php if (can('reports.stock')): ?><a href="<?= url('reports/stock') ?>" class="btn btn-soft btn-sm">Report</a><?php endif; ?></div>
            <div class="card-body py-2">
                <?php if (!$lowStock): ?><p class="text-muted-2 mb-0 py-3 text-center">All products well stocked.</p><?php endif; ?>
                <?php foreach ($lowStock as $p): ?>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color:var(--border)!important;">
                        <div><div class="fw-800" style="font-size:.88rem;"><?= e($p['name']) ?></div><div class="text-muted-2" style="font-size:.75rem;"><?= e($p['sku']) ?></div></div>
                        <span class="badge-pill <?= (float) $p['qty'] <= 0 ? 'badge-danger' : 'badge-warning' ?>"><?= (float) $p['qty'] <= 0 ? 'Out of stock' : ((int) $p['qty'] . ' left') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
$trendJson    = json_encode($trend, JSON_HEX_APOS | JSON_HEX_QUOT);
$paymentsJson = json_encode($payments, JSON_HEX_APOS | JSON_HEX_QUOT);
$showSales    = $canDashSales ? 'true' : 'false';
$showPurchase = $canDashPurchase ? 'true' : 'false';
$showProfit   = $canDashProfit ? 'true' : 'false';
$pageScript = <<<JS
(function(){
    const form = document.getElementById('dashboardFilterForm');
    const period = document.getElementById('dashPeriod');
    if (form && period) {
        const customBlocks = form.querySelectorAll('.report-custom-dates');
        const applyWrap = document.getElementById('dashApplyWrap');
        function toggleCustom() {
            const show = period.value === 'custom';
            customBlocks.forEach(el => { el.style.display = show ? '' : 'none'; });
            if (applyWrap) {
                applyWrap.classList.toggle('d-none', !show);
                applyWrap.classList.toggle('d-grid', show);
            }
        }
        period.addEventListener('change', function () {
            toggleCustom();
            if (period.value !== 'custom') form.submit();
        });
        toggleCustom();
    }

    const css = getComputedStyle(document.body);
    const grid = css.getPropertyValue('--border');
    const text = css.getPropertyValue('--text-muted');
    const showSales = {$showSales};
    const showPurchase = {$showPurchase};
    const showProfit = {$showProfit};
    let salesChart, paymentChart;

    function buildSales(t){
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;
        const mk = (c) => { const g = ctx.getContext('2d').createLinearGradient(0,0,0,260); g.addColorStop(0,c+'66'); g.addColorStop(1,c+'00'); return g; };
        const datasets = [];
        if (showSales && t.sales) {
            datasets.push({label:'Sales', data:t.sales, borderColor:'#dc2626', backgroundColor:mk('#dc2626'), fill:true, tension:.4, borderWidth:2, pointRadius:0});
        }
        if (showPurchase && t.purchases) {
            datasets.push({label:'Purchase', data:t.purchases, borderColor:'#0ea5e9', backgroundColor:mk('#0ea5e9'), fill:true, tension:.4, borderWidth:2, pointRadius:0});
        }
        if (showProfit && t.profit) {
            datasets.push({label:'Profit', data:t.profit, borderColor:'#10b981', backgroundColor:mk('#10b981'), fill:true, tension:.4, borderWidth:2, pointRadius:0});
        }
        if (!datasets.length) {
            datasets.push({label:'No data', data:(t.labels||[]).map(()=>0), borderColor:'#94a3b8', backgroundColor:mk('#94a3b8'), fill:false, tension:.4, borderWidth:1, pointRadius:0});
        }
        const cfg = {
            type:'line',
            data:{ labels:t.labels, datasets },
            options:{ responsive:true, maintainAspectRatio:true,
                plugins:{ legend:{ labels:{ usePointStyle:true, color:text, boxWidth:8 } } },
                scales:{ x:{ grid:{display:false}, ticks:{color:text} }, y:{ grid:{color:grid}, ticks:{color:text} } } }
        };
        if(salesChart) salesChart.destroy();
        salesChart = new Chart(ctx, cfg);
    }
    function buildPayment(p){
        const ctx = document.getElementById('paymentChart');
        if (!ctx) return;
        const cfg = {
            type:'doughnut',
            data:{ labels:p.labels.length?p.labels:['No data'], datasets:[{ data:p.data.length?p.data:[1], backgroundColor:['#dc2626','#991b1b','#0a0a0a','#ef4444','#7f1d1d','#262626'], borderWidth:0 }] },
            options:{ cutout:'68%', plugins:{ legend:{ position:'bottom', labels:{ usePointStyle:true, color:text, boxWidth:8, padding:14 } } } }
        };
        if(paymentChart) paymentChart.destroy();
        paymentChart = new Chart(ctx, cfg);
    }

    buildSales({$trendJson});
    buildPayment({$paymentsJson});
})();
JS;
?>
