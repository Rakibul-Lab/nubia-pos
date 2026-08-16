<?php
use App\Core\View;
/** @var array $rows @var float $in @var float $out @var float $balance @var string $start @var string $end @var string $method */
?>
<?= View::partial('components.page_head', ['title' => $title, 'subtitle' => ucfirst($method) . ' transactions']) ?>
<?= View::partial('accounting.nav') ?>
<form method="GET" class="card mb-3"><div class="card-body d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label">From</label><input type="date" name="start" class="form-control" value="<?= e($start) ?>"></div>
    <div><label class="form-label">To</label><input type="date" name="end" class="form-control" value="<?= e($end) ?>"></div>
    <button class="btn btn-brand"><span class="material-symbols-rounded">filter_alt</span> Filter</button>
</div></form>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><p class="text-muted-2 mb-1">Money In</p><h3 class="fw-800" style="color:var(--success)"><?= money($in) ?></h3></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><p class="text-muted-2 mb-1">Money Out</p><h3 class="fw-800" style="color:var(--danger)"><?= money($out) ?></h3></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><p class="text-muted-2 mb-1">Balance</p><h3 class="fw-800"><?= money($balance) ?></h3></div></div></div>
</div>

<div class="card"><div class="table-wrap"><table class="nubia">
    <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th>Note</th><th class="text-end">In</th><th class="text-end">Out</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td class="text-muted-2"><?= date('M j, Y', strtotime($r['date'])) ?></td>
            <td class="fw-800"><?= e($r['reference']) ?></td>
            <td><span class="badge-pill badge-muted"><?= e(ucfirst((string) $r['payable_type'])) ?></span></td>
            <td class="text-muted-2" style="font-size:.82rem;"><?= e($r['note'] ?? '') ?></td>
            <td class="text-end" style="color:var(--success)"><?= $r['direction'] === 'in' ? money((float) $r['amount']) : '—' ?></td>
            <td class="text-end" style="color:var(--danger)"><?= $r['direction'] === 'out' ? money((float) $r['amount']) : '—' ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($rows === []): ?><tr><td colspan="6"><?= View::partial('components.empty', ['message' => 'No transactions in this period']) ?></td></tr><?php endif; ?>
    </tbody>
</table></div></div>
