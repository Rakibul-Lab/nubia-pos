<?php
use App\Core\View;
/** @var array $txn */
$revenue = (float) $txn['selling_price'] * (float) $txn['quantity'];
$canCosts = can('costs.view');
$canProfit = can('profit.view');
?>
<?= View::partial('components.page_head', [
    'title'    => 'Direct Buy & Sell · ' . e($txn['reference']),
    'subtitle' => date('F j, Y g:i A', strtotime($txn['created_at'])),
    'extraActions' => can('direct.invoice') ? '<a href="' . url('direct-buy-sell/' . $txn['id'] . '/invoice') . '" target="_blank" class="btn btn-soft"><span class="material-symbols-rounded">print</span> Invoice</a>' : '',
]) ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header">Transaction Details</div><div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted-2" style="font-size:.78rem;">Product</div><div class="fw-800"><?= e($txn['product_name']) ?></div></div>
                <div class="col-md-3"><div class="text-muted-2" style="font-size:.78rem;">Quantity</div><div class="fw-800"><?= rtrim(rtrim(number_format((float) $txn['quantity'], 2), '0'), '.') ?></div></div>
                <div class="col-md-3"><div class="text-muted-2" style="font-size:.78rem;">Added to Inventory</div><div class="fw-800"><?= $txn['add_to_inventory'] ? 'Yes' : 'No' ?></div></div>
                <div class="col-md-6"><div class="text-muted-2" style="font-size:.78rem;">Supplier</div><div class="fw-800"><?= e($txn['supplier_name'] ?? '—') ?></div></div>
                <div class="col-md-6"><div class="text-muted-2" style="font-size:.78rem;">Customer</div><div class="fw-800"><?= e($txn['customer_name'] ?? 'Walk-in') ?> <?= $txn['customer_phone'] ? '· ' . e($txn['customer_phone']) : '' ?></div></div>
            </div>
        </div></div>
        <div class="card"><div class="card-header"><?= $canCosts ? 'Cost Breakdown' : 'Sale Summary' ?></div>
            <div class="table-wrap"><table class="nubia">
                <tbody>
                    <?php if ($canCosts): ?>
                    <tr><td>Supplier Purchase Price (per unit)</td><td class="text-end"><?= money($txn['purchase_price']) ?></td></tr>
                    <tr><td>Transportation Cost</td><td class="text-end"><?= money($txn['transportation_cost']) ?></td></tr>
                    <tr><td>Additional Cost</td><td class="text-end"><?= money($txn['additional_cost']) ?></td></tr>
                    <tr><td class="fw-800">Total Cost (× <?= rtrim(rtrim(number_format((float) $txn['quantity'], 2), '0'), '.') ?>)</td><td class="text-end fw-800"><?= money($txn['total_cost']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td>Selling Price (per unit)</td><td class="text-end"><?= money($txn['selling_price']) ?></td></tr>
                    <?php if (can('revenue.view')): ?>
                    <tr><td class="fw-800">Total Revenue</td><td class="text-end fw-800"><?= money($revenue) ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body text-center">
            <?php if ($canProfit): ?>
            <div class="stat-icon bg-grad-2 mx-auto mb-2" style="width:56px;height:56px;"><span class="material-symbols-rounded" style="font-size:30px;">trending_up</span></div>
            <div class="text-muted-2">Net Profit</div>
            <div class="fw-800" style="font-size:2.4rem;color:var(--success);"><?= money($txn['profit']) ?></div>
            <hr class="divider">
            <?php endif; ?>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Paid</span><span class="fw-800"><?= money($txn['paid']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Due</span><span class="fw-800 <?= (float) $txn['due'] > 0 ? 'text-danger' : '' ?>"><?= money($txn['due']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Payment</span><span><?= e(payment_method_label($txn['payment_method'])) ?></span></div>
            <div class="mt-3 d-flex gap-2 justify-content-center">
                <?php if (can('purchases.view')): ?><a href="<?= url('purchases/' . $txn['purchase_id']) ?>" class="btn btn-soft btn-sm">View Purchase</a><?php endif; ?>
                <?php if (can('sales.view')): ?><a href="<?= url('sales/' . $txn['sale_id']) ?>" class="btn btn-soft btn-sm">View Sale</a><?php endif; ?>
            </div>
        </div></div>
    </div>
</div>
