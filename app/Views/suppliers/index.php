<?php
use App\Core\View;
/** @var array $suppliers @var array $meta @var string $keyword */
?>
<?= View::partial('components.page_head', [
    'title'       => 'Suppliers',
    'subtitle'    => number_format($meta['total']) . ' suppliers',
    'actionUrl'   => can('suppliers.create') ? url('suppliers/create') : null,
    'actionLabel' => 'Add Supplier',
]) ?>

<div class="card mb-3"><div class="card-body py-3">
    <form class="row g-2" method="GET" action="<?= url('suppliers') ?>">
        <div class="col-md-10"><div class="input-group"><span class="input-group-text"><span class="material-symbols-rounded">search</span></span>
            <input type="text" name="q" class="form-control" placeholder="Search name, phone or company…" value="<?= e($keyword) ?>"></div></div>
        <div class="col-md-2 d-grid"><button class="btn btn-brand">Search</button></div>
    </form>
</div></div>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Supplier</th><th>Phone</th><th>Company</th><th>Total Purchased</th><th>Payable</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$suppliers): ?><tr><td colspan="6"><?= View::partial('components.empty', ['icon' => 'diversity_3', 'title' => 'No suppliers']) ?></td></tr><?php endif; ?>
            <?php foreach ($suppliers as $s): ?>
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><div class="avatar sm"><?= e(strtoupper(substr($s['name'], 0, 1))) ?></div>
                        <div><div class="fw-800"><?= e($s['name']) ?></div><div class="text-muted-2" style="font-size:.75rem;"><?= e($s['email'] ?? '') ?></div></div></div></td>
                    <td><?= e($s['phone'] ?? '—') ?></td>
                    <td><?= e($s['company'] ?? '—') ?></td>
                    <td class="fw-800"><?= money($s['total_purchased']) ?></td>
                    <td><?php $due = (float) $s['due_amount'] + (float) $s['opening_balance']; ?><span class="badge-pill <?= $due > 0 ? 'badge-warning' : 'badge-success' ?>"><?= money($due) ?></span></td>
                    <td class="text-end">
                        <a href="<?= url('suppliers/' . $s['id']) ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a>
                        <?php if (can('suppliers.edit')): ?><a href="<?= url('suppliers/' . $s['id'] . '/edit') ?>" class="icon-btn" style="width:34px;height:34px;display:inline-grid;"><span class="material-symbols-rounded" style="font-size:18px;">edit</span></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('suppliers') . '?q=' . urlencode($keyword)]) ?></div>
</div>
