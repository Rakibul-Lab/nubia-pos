<?php
use App\Core\View;
/** @var array $quote @var array $items */
?>
<?= View::partial('components.page_head', ['title' => $quote['reference'], 'subtitle' => 'Quotation details', 'extraActions' => '<button onclick="window.print()" class="btn btn-soft"><span class="material-symbols-rounded">print</span> Print</button>']) ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Product</th><th>SKU</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr><td class="fw-800"><?= e($it['product_name']) ?></td><td class="text-muted-2"><?= e($it['sku']) ?></td><td class="text-end"><?= number_format((float) $it['quantity'], 2) ?></td><td class="text-end"><?= money((float) $it['unit_price']) ?></td><td class="text-end fw-800"><?= money((float) $it['subtotal']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Customer</div><div class="card-body">
            <div class="fw-800"><?= e($quote['customer_name'] ?? 'Walk-in') ?></div>
            <div class="text-muted-2"><?= e($quote['customer_phone'] ?? '') ?></div>
        </div></div>
        <div class="card"><div class="card-header">Summary</div><div class="card-body">
            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Subtotal</span><span><?= money((float) $quote['subtotal']) ?></span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Discount</span><span>−<?= money((float) $quote['discount']) ?></span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Tax</span><span><?= money((float) $quote['tax']) ?></span></div>
            <div class="d-flex justify-content-between py-2 mt-1" style="border-top:2px solid var(--brand);"><span class="fw-800">Total</span><span class="fw-800" style="font-size:1.15rem;"><?= money((float) $quote['total']) ?></span></div>
            <?php if (!empty($quote['note'])): ?><p class="text-muted-2 mt-2 mb-0" style="font-size:.85rem;"><?= e($quote['note']) ?></p><?php endif; ?>
        </div></div>
    </div>
</div>
