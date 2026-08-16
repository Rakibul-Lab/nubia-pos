<?php
use App\Core\View;
/** @var array $customer @var array $ledger @var float $balance @var array $sales */
$customerActions = '';
if (can('customers.payment')) {
    $customerActions .= '<button class="btn btn-brand" onclick="collectModal.show()"><span class="material-symbols-rounded">payments</span> Collect Payment</button> ';
}
if (can('customers.edit')) {
    $customerActions .= '<a href="' . url('customers/' . $customer['id'] . '/edit') . '" class="btn btn-soft"><span class="material-symbols-rounded">edit</span> Edit</a>';
}
?>
<?= View::partial('components.page_head', [
    'title'    => $customer['name'],
    'subtitle' => e($customer['phone'] ?? '') . ' · ' . ucfirst($customer['type']),
    'extraActions' => $customerActions,
]) ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body text-center">
            <div class="avatar lg mx-auto mb-2"><?= e(strtoupper(substr($customer['name'], 0, 1))) ?></div>
            <h5 class="fw-800 mb-0"><?= e($customer['name']) ?></h5>
            <p class="text-muted-2"><?= e($customer['company'] ?? '') ?></p>
            <div class="glass p-3 mb-2"><div class="text-muted-2" style="font-size:.75rem;">Outstanding Due</div><div class="fw-800" style="font-size:1.4rem;color:var(--danger);"><?= money($balance) ?></div></div>
            <div class="text-start mt-3" style="font-size:.85rem;">
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Phone</span><span><?= e($customer['phone'] ?? '—') ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Email</span><span><?= e($customer['email'] ?? '—') ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Address</span><span><?= e($customer['address'] ?? '—') ?></span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Loyalty Points</span><span class="fw-800"><?= (int) $customer['loyalty_points'] ?></span></div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header">Account Ledger</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
                <tbody>
                <?php if (!$ledger): ?><tr><td colspan="5" class="text-center text-muted-2 py-3">No transactions.</td></tr><?php endif; ?>
                <?php $run = (float) $customer['opening_balance']; foreach ($ledger as $l): $run += (float) $l['debit'] - (float) $l['credit']; ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($l['date'])) ?></td>
                        <td><?= e($l['ref']) ?></td>
                        <td><span class="badge-pill <?= $l['type'] === 'Sale' ? 'badge-info' : 'badge-success' ?>"><?= e($l['type']) ?></span></td>
                        <td class="text-end"><?= (float) $l['debit'] ? money($l['debit']) : '—' ?></td>
                        <td class="text-end"><?= (float) $l['credit'] ? money($l['credit']) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php if (can('sales.view')): ?><div class="card"><div class="card-header">Recent Sales</div>
            <div class="table-wrap"><table class="nubia">
                <thead><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$sales): ?><tr><td colspan="5" class="text-center text-muted-2 py-3">No sales.</td></tr><?php endif; ?>
                <?php foreach ($sales as $s): ?>
                    <tr><td><a href="<?= url('sales/' . $s['id']) ?>"><?= e($s['invoice_no']) ?></a></td><td><?= date('M j, Y', strtotime($s['sale_date'])) ?></td><td><?= money($s['total']) ?></td><td><?= money($s['due']) ?></td>
                        <td><span class="badge-pill <?= $s['payment_status'] === 'paid' ? 'badge-success' : ($s['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($s['payment_status']) ?></span></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div><?php endif; ?>
    </div>
</div>

<?php if (can('customers.payment')): ?><div class="modal fade" id="collectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="POST" action="<?= url('customers/' . $customer['id'] . '/payment') ?>"><?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Collect Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" max="<?= $balance ?>" required></div>
            <div class="mb-1"><label class="form-label">Method</label><select name="method" class="form-select"><?php foreach (payment_methods(false) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Collect</button></div>
    </form>
</div></div></div><?php endif; ?>

<?php if (can('customers.payment')) $pageScript = "const collectModal = new bootstrap.Modal('#collectModal');"; ?>
