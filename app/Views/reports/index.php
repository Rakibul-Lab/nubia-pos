<?php
use App\Core\View;
$reports = [
    ['sales', 'Sales Report', 'point_of_sale', 'bg-grad-1', 'Sales performance & revenue', 'reports.sales'],
    ['purchases', 'Purchase Report', 'local_shipping', 'bg-grad-4', 'Supplier purchases & dues', 'reports.purchases'],
    ['profit', 'Profit & Loss', 'trending_up', 'bg-grad-2', 'Gross & net profit analysis', 'reports.profit'],
    ['expenses', 'Expense Report', 'payments', 'bg-grad-3', 'All business expenses', 'reports.expenses'],
    ['stock', 'Stock Report', 'inventory_2', 'bg-grad-6', 'Inventory valuation & alerts', 'reports.stock'],
    ['customers', 'Customer Report', 'groups', 'bg-grad-5', 'Customer sales & dues', 'reports.customers'],
    ['suppliers', 'Supplier Report', 'diversity_3', 'bg-grad-1', 'Supplier purchases & payables', 'reports.suppliers'],
];
?>
<?= View::partial('components.page_head', ['title' => 'Reports', 'subtitle' => 'Generate, print and export detailed business reports']) ?>
<div class="row g-3">
    <?php foreach ($reports as $r): ?>
        <?php if (!can($r[5])) continue; ?>
        <div class="col-md-6 col-lg-4">
            <a href="<?= url('reports/' . $r[0]) ?>" class="card h-100" style="text-decoration:none;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon <?= $r[3] ?>" style="width:52px;height:52px;flex-shrink:0;"><span class="material-symbols-rounded"><?= $r[2] ?></span></div>
                    <div>
                        <div class="fw-800" style="color:var(--text);"><?= e($r[1]) ?></div>
                        <div class="text-muted-2" style="font-size:.82rem;"><?= e($r[4]) ?></div>
                    </div>
                    <span class="material-symbols-rounded ms-auto text-muted-2">chevron_right</span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
