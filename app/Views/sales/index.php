<?php
use App\Core\View;
/** @var array $sales @var array $meta @var string $keyword @var string $status */
?>
<?= View::partial('components.page_head', [
    'title'       => 'Sales',
    'subtitle'    => number_format($meta['total']) . ' invoices',
    'actionUrl'   => can('sales.create') ? url('sales/create') : null,
    'actionLabel' => 'New Sale',
    'extraActions' => can('pos.use') ? '<a href="' . url('pos') . '" class="btn btn-soft"><span class="material-symbols-rounded">point_of_sale</span> POS</a>' : '',
]) ?>

<div class="card mb-3"><div class="card-body py-3">
    <form class="row g-2" method="GET" action="<?= url('sales') ?>">
        <div class="col-md-7"><div class="input-group"><span class="input-group-text"><span class="material-symbols-rounded">search</span></span>
            <input type="text" name="q" class="form-control" placeholder="Search invoice, customer or phone…" value="<?= e($keyword) ?>"></div></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">All Status</option>
            <?php foreach (['paid', 'partial', 'unpaid'] as $st): ?><option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-brand">Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$sales): ?><tr><td colspan="8"><?= View::partial('components.empty', ['icon' => 'receipt_long', 'title' => 'No sales yet']) ?></td></tr><?php endif; ?>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td class="fw-800"><?= e($s['invoice_no']) ?></td>
                    <td><?= e($s['customer_name']) ?></td>
                    <td><?= date('M j, Y', strtotime($s['sale_date'])) ?></td>
                    <td class="fw-800"><?= money($s['total']) ?></td>
                    <td class="text-success"><?= money($s['paid']) ?></td>
                    <td class="<?= (float) $s['due'] > 0 ? 'text-danger' : '' ?>"><?= money($s['due']) ?></td>
                    <td><span class="badge-pill <?= $s['payment_status'] === 'paid' ? 'badge-success' : ($s['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($s['payment_status']) ?></span></td>
                    <td class="text-end">
                        <a href="<?= url('sales/' . $s['id']) ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a>
                        <?php if (can('sales.invoice')): ?>
                            <a href="<?= url('sales/' . $s['id'] . '/thermal') ?>" target="_blank" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">receipt</span></a>
                            <a href="<?= url('sales/' . $s['id'] . '/invoice') ?>" target="_blank" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">print</span></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('sales') . '?q=' . urlencode($keyword)]) ?></div>
</div>
