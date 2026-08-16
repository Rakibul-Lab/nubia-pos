<?php
use App\Core\View;
/** @var array $purchases @var array $meta @var string $keyword @var string $status */
$canCosts = can('costs.view');
$colspan = $canCosts ? 8 : 5;
?>
<?= View::partial('components.page_head', [
    'title'       => 'Purchases',
    'subtitle'    => number_format($meta['total']) . ' purchase records',
    'actionUrl'   => can('purchases.create') ? url('purchases/create') : null,
    'actionLabel' => 'New Purchase',
]) ?>

<div class="card mb-3"><div class="card-body py-3">
    <form class="row g-2" method="GET" action="<?= url('purchases') ?>">
        <div class="col-md-7"><div class="input-group"><span class="input-group-text"><span class="material-symbols-rounded">search</span></span>
            <input type="text" name="q" class="form-control" placeholder="Search reference or supplier…" value="<?= e($keyword) ?>"></div></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">All Payment Status</option>
            <?php foreach (['paid', 'partial', 'unpaid'] as $st): ?><option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-brand">Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Reference</th><th>Supplier</th><th>Date</th><?php if ($canCosts): ?><th>Total</th><th>Paid</th><th>Due</th><?php endif; ?><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$purchases): ?><tr><td colspan="<?= $colspan ?>"><?= View::partial('components.empty', ['icon' => 'local_shipping', 'title' => 'No purchases']) ?></td></tr><?php endif; ?>
            <?php foreach ($purchases as $p): ?>
                <tr>
                    <td class="fw-800"><?= e($p['reference']) ?></td>
                    <td><?= e($p['supplier_name'] ?? 'Cash Purchase') ?></td>
                    <td><?= date('M j, Y', strtotime($p['purchase_date'])) ?></td>
                    <?php if ($canCosts): ?>
                        <td class="fw-800"><?= money($p['total']) ?></td>
                        <td class="text-success"><?= money($p['paid']) ?></td>
                        <td class="<?= (float) $p['due'] > 0 ? 'text-danger' : '' ?>"><?= money($p['due']) ?></td>
                    <?php endif; ?>
                    <td><span class="badge-pill <?= $p['payment_status'] === 'paid' ? 'badge-success' : ($p['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($p['payment_status']) ?></span></td>
                    <td class="text-end">
                        <a href="<?= url('purchases/' . $p['id']) ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a>
                        <?php if (can('purchases.invoice') && $canCosts): ?><a href="<?= url('purchases/' . $p['id'] . '/invoice') ?>" target="_blank" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">print</span></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('purchases') . '?q=' . urlencode($keyword)]) ?></div>
</div>
