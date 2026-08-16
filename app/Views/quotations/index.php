<?php
use App\Core\View;
/** @var array $quotations */
$badge = ['draft' => 'badge-muted', 'sent' => 'badge-info', 'accepted' => 'badge-success', 'declined' => 'badge-danger', 'converted' => 'badge-success'];
?>
<?= View::partial('components.page_head', ['title' => 'Quotations', 'subtitle' => count($quotations) . ' estimates', 'actionUrl' => can('quotations.create') ? url('quotations/create') : null, 'actionLabel' => 'New Quotation']) ?>
<div class="card"><div class="table-wrap"><table class="nubia">
    <thead><tr><th>Reference</th><th>Customer</th><th>Date</th><th>Valid Until</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($quotations as $q): ?>
        <tr>
            <td class="fw-800"><?= e($q['reference']) ?></td>
            <td><?= e($q['customer_name']) ?></td>
            <td class="text-muted-2"><?= date('M j, Y', strtotime($q['quote_date'])) ?></td>
            <td class="text-muted-2"><?= $q['valid_until'] ? date('M j, Y', strtotime($q['valid_until'])) : '—' ?></td>
            <td class="text-end fw-800"><?= money((float) $q['total']) ?></td>
            <td><span class="badge-pill <?= $badge[$q['status']] ?? 'badge-muted' ?>"><?= ucfirst($q['status']) ?></span></td>
            <td class="text-end"><a href="<?= url('quotations/' . $q['id']) ?>" class="btn btn-soft btn-sm">View</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($quotations === []): ?><tr><td colspan="7"><?= View::partial('components.empty', ['message' => 'No quotations yet']) ?></td></tr><?php endif; ?>
    </tbody>
</table></div></div>
