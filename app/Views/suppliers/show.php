<?php
use App\Core\View;
/** @var array $supplier @var array $ledger @var float $balance @var array $purchases */
$supplierActions = '';
if (can('suppliers.payment')) {
    $supplierActions .= '<button class="btn btn-brand" onclick="payModal.show()"><span class="material-symbols-rounded">payments</span> Make Payment</button> ';
}
if (can('suppliers.edit')) {
    $supplierActions .= '<a href="' . url('suppliers/' . $supplier['id'] . '/edit') . '" class="btn btn-soft"><span class="material-symbols-rounded">edit</span> Edit</a>';
}
?>
<?= View::partial('components.page_head', [
    'title'    => $supplier['name'],
    'subtitle' => e($supplier['phone'] ?? '') . ' · ' . e($supplier['company'] ?? ''),
    'extraActions' => $supplierActions,
]) ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body text-center">
            <div class="avatar lg mx-auto mb-2"><?= e(strtoupper(substr($supplier['name'], 0, 1))) ?></div>
            <h5 class="fw-800 mb-0"><?= e($supplier['name']) ?></h5>
            <p class="text-muted-2"><?= e($supplier['company'] ?? '') ?></p>
            <div class="glass p-3 mb-2"><div class="text-muted-2" style="font-size:.75rem;">Total Payable</div><div class="fw-800" style="font-size:1.4rem;color:var(--warning);"><?= money($balance) ?></div></div>
            <div class="text-start mt-3" style="font-size:.85rem;">
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Phone</span><span><?= e($supplier['phone'] ?? '—') ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Email</span><span><?= e($supplier['email'] ?? '—') ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Address</span><span><?= e($supplier['address'] ?? '—') ?></span></div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header">Account Ledger</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
                <tbody>
                <?php if (!$ledger): ?><tr><td colspan="5" class="text-center text-muted-2 py-3">No transactions.</td></tr><?php endif; ?>
                <?php foreach ($ledger as $l): ?>
                    <tr><td><?= date('M j, Y', strtotime($l['date'])) ?></td><td><?= e($l['ref']) ?></td>
                        <td><span class="badge-pill <?= $l['type'] === 'Purchase' ? 'badge-info' : 'badge-success' ?>"><?= e($l['type']) ?></span></td>
                        <td class="text-end"><?= (float) $l['debit'] ? money($l['debit']) : '—' ?></td>
                        <td class="text-end"><?= (float) $l['credit'] ? money($l['credit']) : '—' ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php if (can('purchases.view')): ?><div class="card"><div class="card-header">Recent Purchases</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Reference</th><th>Date</th><th>Total</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$purchases): ?><tr><td colspan="5" class="text-center text-muted-2 py-3">No purchases.</td></tr><?php endif; ?>
                <?php foreach ($purchases as $p): ?>
                    <tr><td><a href="<?= url('purchases/' . $p['id']) ?>"><?= e($p['reference']) ?></a></td><td><?= date('M j, Y', strtotime($p['purchase_date'])) ?></td><td><?= money($p['total']) ?></td><td><?= money($p['due']) ?></td>
                        <td><span class="badge-pill <?= $p['payment_status'] === 'paid' ? 'badge-success' : ($p['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($p['payment_status']) ?></span></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div><?php endif; ?>
    </div>
</div>

<?php if (can('suppliers.payment')): ?><div class="modal fade" id="payModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="POST" action="<?= url('suppliers/' . $supplier['id'] . '/payment') ?>"><?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Make Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="mb-1"><label class="form-label">Method</label><select name="method" class="form-select"><?php foreach (payment_methods(false) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Pay</button></div>
    </form>
</div></div></div><?php endif; ?>

<?php if (can('suppliers.payment')) $pageScript = "const payModal = new bootstrap.Modal('#payModal');"; ?>
