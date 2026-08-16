<?php
use App\Core\View;
/** @var array $purchase @var array $items */
$canCosts = can('costs.view');
?>
<?= View::partial('components.page_head', [
    'title'    => 'Purchase ' . e($purchase['reference']),
    'subtitle' => date('F j, Y', strtotime($purchase['purchase_date'])),
    'extraActions' => ($canCosts && can('purchases.invoice') ? '<a href="' . url('purchases/' . $purchase['id'] . '/invoice') . '" target="_blank" class="btn btn-soft"><span class="material-symbols-rounded">print</span> Print</a>' : '')
        . ((float) $purchase['due'] > 0 && can('purchases.payment') ? ' <button class="btn btn-brand" onclick="payModal.show()"><span class="material-symbols-rounded">payments</span> Add Payment</button>' : ''),
]) ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Items</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Product</th><th>Qty</th><?php if ($canCosts): ?><th>Unit Cost</th><th class="text-end">Subtotal</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr><td><div class="fw-800"><?= e($it['product_name']) ?></div><div class="text-muted-2" style="font-size:.75rem;"><?= e($it['sku']) ?></div></td>
                        <td><?= rtrim(rtrim(number_format((float) $it['quantity'], 2), '0'), '.') ?></td>
                        <?php if ($canCosts): ?><td><?= money($it['unit_cost']) ?></td><td class="text-end fw-800"><?= money($it['subtotal']) ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Supplier</div><div class="card-body">
            <div class="fw-800"><?= e($purchase['supplier_name'] ?? 'Cash Purchase') ?></div>
            <div class="text-muted-2" style="font-size:.85rem;"><?= e($purchase['supplier_phone'] ?? '') ?></div>
            <div class="text-muted-2" style="font-size:.85rem;"><?= e($purchase['supplier_address'] ?? '') ?></div>
        </div></div>
        <?php if ($canCosts): ?>
        <div class="card"><div class="card-header">Summary</div><div class="card-body">
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Subtotal</span><span><?= money($purchase['subtotal']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Discount</span><span>- <?= money($purchase['discount']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Tax</span><span><?= money($purchase['tax']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Shipping</span><span><?= money($purchase['shipping']) ?></span></div>
            <hr class="divider">
            <div class="d-flex justify-content-between py-1"><span class="fw-800">Total</span><span class="fw-800"><?= money($purchase['total']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-success">Paid</span><span class="text-success"><?= money($purchase['paid']) ?></span></div>
            <div class="d-flex justify-content-between py-1"><span class="text-danger">Due</span><span class="text-danger fw-800"><?= money($purchase['due']) ?></span></div>
        </div></div>
        <?php else: ?>
        <div class="card"><div class="card-header">Payment</div><div class="card-body">
            <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Status</span><span class="badge-pill <?= $purchase['payment_status'] === 'paid' ? 'badge-success' : ($purchase['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($purchase['payment_status']) ?></span></div>
        </div></div>
        <?php endif; ?>
    </div>
</div>

<?php if ((float) $purchase['due'] > 0 && can('purchases.payment')): ?><div class="modal fade" id="payModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="POST" action="<?= url('purchases/' . $purchase['id'] . '/payment') ?>"><?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Add Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Amount<?= $canCosts ? ' (Due: ' . money($purchase['due']) . ')' : '' ?></label><input type="number" step="0.01" name="amount" class="form-control" max="<?= $purchase['due'] ?>" required></div>
            <div class="mb-1"><label class="form-label">Method</label><select name="method" class="form-select"><?php foreach (payment_methods(false) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Pay</button></div>
    </form>
</div></div></div><?php endif; ?>
<?php $pageScript = "if(document.getElementById('payModal')){window.payModal=new bootstrap.Modal('#payModal');}"; ?>
