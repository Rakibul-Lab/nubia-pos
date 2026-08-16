<?php
use App\Core\View;
/** @var array $customers @var array $meta @var string $keyword */
?>
<?= View::partial('components.page_head', [
    'title'       => 'Customers',
    'subtitle'    => number_format($meta['total']) . ' customers',
    'actionUrl'   => can('customers.create') ? url('customers/create') : null,
    'actionLabel' => 'Add Customer',
]) ?>

<div class="card mb-3"><div class="card-body py-3">
    <form class="row g-2" method="GET" action="<?= url('customers') ?>">
        <div class="col-md-10"><div class="input-group"><span class="input-group-text"><span class="material-symbols-rounded">search</span></span>
            <input type="text" name="q" class="form-control" placeholder="Search name, phone or email…" value="<?= e($keyword) ?>"></div></div>
        <div class="col-md-2 d-grid"><button class="btn btn-brand">Search</button></div>
    </form>
</div></div>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Customer</th><th>Phone</th><th>Type</th><th>Total Purchased</th><th>Due</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$customers): ?><tr><td colspan="6"><?= View::partial('components.empty', ['icon' => 'groups', 'title' => 'No customers']) ?></td></tr><?php endif; ?>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><div class="avatar sm"><?= e(strtoupper(substr($c['name'], 0, 1))) ?></div>
                        <div><div class="fw-800"><?= e($c['name']) ?></div><div class="text-muted-2" style="font-size:.75rem;"><?= e($c['email'] ?? '') ?></div></div></div></td>
                    <td><?= e($c['phone'] ?? '—') ?></td>
                    <td><span class="badge-pill <?= $c['type'] === 'wholesale' ? 'badge-info' : 'badge-muted' ?>"><?= ucfirst($c['type']) ?></span></td>
                    <td class="fw-800"><?= money($c['total_purchased']) ?></td>
                    <td><?php $due = (float) $c['due_amount'] + (float) $c['opening_balance']; ?><span class="badge-pill <?= $due > 0 ? 'badge-danger' : 'badge-success' ?>"><?= money($due) ?></span></td>
                    <td class="text-end">
                        <a href="<?= url('customers/' . $c['id']) ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a>
                        <?php if (can('customers.edit')): ?><a href="<?= url('customers/' . $c['id'] . '/edit') ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">edit</span></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('customers') . '?q=' . urlencode($keyword)]) ?></div>
</div>
