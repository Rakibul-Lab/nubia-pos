<?php
use App\Core\View;
/** @var array $sale @var array $items @var array $exchanges @var bool $canExchange @var array|null $exchangeContext */
$exchanges = $exchanges ?? [];
$canExchange = $canExchange ?? false;
$exchangeContext = $exchangeContext ?? null;
$isExchangeInvoice = !empty($exchangeContext['is_exchange']);

$subtitle = date('F j, Y g:i A', strtotime($sale['created_at'])) . ' · by ' . e($sale['cashier'] ?? 'System');
if ($isExchangeInvoice) {
    $subtitle = 'Exchange invoice' . (!empty($exchangeContext['original_invoice']) ? ' · from ' . e($exchangeContext['original_invoice']) : '') . ' · ' . $subtitle;
}

$actions = '';
if (can('sales.invoice')) {
    $actions .= '<a href="' . url('sales/' . $sale['id'] . '/thermal') . '" target="_blank" class="btn btn-soft"><span class="material-symbols-rounded">receipt</span> Thermal</a> '
        . '<a href="' . url('sales/' . $sale['id'] . '/invoice') . '" target="_blank" class="btn btn-soft"><span class="material-symbols-rounded">print</span> A4</a> '
        . '<a href="' . url('sales/' . $sale['id'] . '/pdf') . '" target="_blank" class="btn btn-soft"><span class="material-symbols-rounded">picture_as_pdf</span> PDF</a>';
}
if ($canExchange && can('sales.exchange')) {
    $actions .= ' <a href="' . url('sales/' . $sale['id'] . '/exchange') . '" class="btn btn-brand"><span class="material-symbols-rounded">swap_horiz</span> Exchange again</a>';
}
if ((float) $sale['due'] > 0 && can('sales.payment')) {
    $actions .= ' <button class="btn btn-soft" onclick="payModal.show()"><span class="material-symbols-rounded">payments</span> Add Payment</button>';
}
?>
<?= View::partial('components.page_head', [
    'title'    => ($isExchangeInvoice ? 'Exchange · ' : 'Invoice ') . e($sale['invoice_no']),
    'subtitle' => $subtitle,
    'extraActions' => $actions,
]) ?>

<?php if ($isExchangeInvoice): ?>
<div class="alert mb-3" style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);border-radius:14px;padding:12px 16px;">
    <div class="fw-800" style="color:var(--brand);">This is an exchange invoice</div>
    <div class="text-muted-2" style="font-size:.85rem;">
        Replacement sale linked to original invoice
        <?php if (!empty($exchangeContext['original_sale_id'])): ?>
            <a href="<?= url('sales/' . $exchangeContext['original_sale_id']) ?>" class="fw-800"><?= e($exchangeContext['original_invoice']) ?></a>
        <?php endif; ?>
        <?php if (!empty($exchangeContext['exchange_ref'])): ?>
            · Ref <?= e($exchangeContext['exchange_ref']) ?>
        <?php endif; ?>
        . You can exchange these items again if needed.
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Items</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Product</th><th>Qty</th><th>Returned</th><th>Still returnable</th><th>Price</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <?php $returnable = (float) ($it['returnable_qty'] ?? ((float) $it['quantity'] - (float) $it['returned_qty'])); ?>
                    <tr>
                        <td>
                            <div class="fw-800"><?= e($it['product_name']) ?></div>
                            <div class="text-muted-2" style="font-size:.75rem;"><?= e($it['sku']) ?></div>
                            <?php if (!empty($it['imeis'])): ?>
                                <div class="mt-1" style="font-size:.72rem;font-family:ui-monospace,monospace;">IMEI: <?= e(implode(', ', $it['imeis'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= rtrim(rtrim(number_format((float) $it['quantity'], 2), '0'), '.') ?></td>
                        <td>
                            <?php if ((float) $it['returned_qty'] > 0): ?>
                                <span class="badge-pill badge-warning"><?= rtrim(rtrim(number_format((float) $it['returned_qty'], 2), '0'), '.') ?></span>
                            <?php else: ?>
                                <span class="text-muted-2">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($returnable > 0): ?>
                                <span class="badge-pill badge-success"><?= rtrim(rtrim(number_format($returnable, 2), '0'), '.') ?></span>
                            <?php else: ?>
                                <span class="text-muted-2">0</span>
                            <?php endif; ?>
                        </td>
                        <td><?= money($it['unit_price']) ?></td>
                        <td class="text-end fw-800"><?= money($it['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>

        <?php if ($exchanges): ?>
        <div class="card mt-3">
            <div class="card-header">Exchange history</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Exchange Ref</th><th>From invoice</th><th>To invoice</th><th>Returned value</th><th>New value</th><th>Settlement</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($exchanges as $ex): ?>
                    <tr>
                        <td class="fw-800"><?= e($ex['reference']) ?></td>
                        <td>
                            <?php if (!empty($ex['original_sale_id'])): ?>
                                <a href="<?= url('sales/' . $ex['original_sale_id']) ?>"><?= e($ex['original_invoice_no'] ?? ('#' . $ex['original_sale_id'])) ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($ex['new_sale_id'])): ?>
                                <a href="<?= url('sales/' . $ex['new_sale_id']) ?>"><?= e($ex['new_invoice_no'] ?? ('#' . $ex['new_sale_id'])) ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= money($ex['return_total']) ?></td>
                        <td><?= money($ex['new_total']) ?></td>
                        <td>
                            <?php if ($ex['settlement'] === 'customer_pays'): ?>
                                <span class="badge-pill badge-warning">Collected <?= money($ex['amount_paid']) ?></span>
                            <?php elseif ($ex['settlement'] === 'refund'): ?>
                                <span class="badge-pill badge-success">Refunded <?= money($ex['amount_refunded']) ?></span>
                            <?php else: ?>
                                <span class="badge-pill badge-success">Even swap</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($ex['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Customer</div><div class="card-body">
            <div class="fw-800"><?= e($sale['customer_name'] ?? 'Walk-in Customer') ?></div>
            <div class="text-muted-2" style="font-size:.85rem;"><?= e($sale['customer_phone'] ?? '') ?></div>
        </div></div>
        <div class="card"><div class="card-header">Summary</div><div class="card-body">
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Subtotal</span><span><?= money($sale['subtotal']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Discount</span><span>- <?= money($sale['discount']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Tax / VAT</span><span><?= money((float) $sale['tax'] + (float) $sale['vat']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Shipping</span><span><?= money($sale['shipping']) ?></span></div>
            <hr class="divider">
            <div class="d-flex justify-content-between py-1"><span class="fw-800">Total</span><span class="fw-800"><?= money($sale['total']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-success">Paid</span><span class="text-success"><?= money($sale['paid']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-danger">Due</span><span class="text-danger fw-800"><?= money($sale['due']) ?></span></div>
            <?php if (can('profit.view')): ?><div class="d-flex justify-content-between py-1"><span class="text-muted-2">Profit</span><span class="badge-pill badge-success"><?= money($sale['profit']) ?></span></div><?php endif; ?>
            <?php if (!empty($sale['note'])): ?>
                <hr class="divider">
                <div class="text-muted-2" style="font-size:.8rem;"><?= e($sale['note']) ?></div>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<?php if ((float) $sale['due'] > 0 && can('sales.payment')): ?><div class="modal fade" id="payModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="POST" action="<?= url('sales/' . $sale['id'] . '/payment') ?>"><?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Add Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Amount (Due: <?= money($sale['due']) ?>)</label><input type="number" step="0.01" name="amount" class="form-control" max="<?= $sale['due'] ?>" required></div>
            <div class="mb-1"><label class="form-label">Method</label><select name="method" class="form-select"><?php foreach (payment_methods(false) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Pay</button></div>
    </form>
</div></div></div><?php endif; ?>
<?php $pageScript = "if(document.getElementById('payModal')){window.payModal=new bootstrap.Modal('#payModal');}"; ?>
