<?php
use App\Core\View;
/** @var array $rows @var string $keyword */
$stockActions = '';
if (can('stock.history')) {
    $stockActions .= '<a href="' . url('stock/history') . '" class="btn btn-soft"><span class="material-symbols-rounded">history</span> History</a>';
}
if (can('stock.transfer')) {
    $stockActions .= '<a href="' . url('stock/transfer') . '" class="btn btn-soft"><span class="material-symbols-rounded">swap_horiz</span> Transfer</a>';
}
if (can('stock.adjust')) {
    $stockActions .= '<a href="' . url('stock/adjustment') . '" class="btn btn-brand"><span class="material-symbols-rounded">tune</span> Adjust</a>';
}
?>
<?= View::partial('components.page_head', [
    'title' => 'Stock Levels',
    'subtitle' => ($warehouseId ?? null)
        ? 'Live inventory for the selected warehouse'
        : 'Live inventory across all warehouses',
    'extraActions' => $stockActions,
]) ?>
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 w-100" style="max-width:620px;">
            <div class="input-group"><span class="input-group-text"><span class="material-symbols-rounded" style="font-size:19px;">search</span></span>
                <input type="text" name="q" class="form-control" placeholder="Search product or SKU..." value="<?= e($keyword) ?>"></div>
            <select name="warehouse" class="form-select" style="max-width:200px;" title="Stock shown for this warehouse">
                <option value="">All Warehouses</option>
                <?php foreach (($warehouses ?? []) as $w): ?>
                    <option value="<?= $w['id'] ?>" <?= ($warehouseId ?? null) === (int) $w['id'] ? 'selected' : '' ?>><?= e($w['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-soft">Search</button>
        </form>
    </div>
    <div class="table-wrap"><table class="nubia">
        <thead><tr><th>Product</th><th>SKU</th><th class="text-end">In Stock</th><th class="text-end">Alert Level</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r):
            $stock = (float) $r['stock']; $alert = (float) $r['alert_quantity'];
            [$cls, $lbl] = $stock <= 0 ? ['badge-danger', 'Out of Stock'] : ($stock <= $alert ? ['badge-warning', 'Low Stock'] : ['badge-success', 'In Stock']); ?>
            <tr>
                <td class="fw-800"><?= e($r['name']) ?></td>
                <td class="text-muted-2"><?= e($r['sku']) ?></td>
                <td class="text-end fw-800"><?= number_format($stock, 2) ?> <span class="text-muted-2" style="font-size:.75rem;"><?= e($r['unit'] ?? '') ?></span></td>
                <td class="text-end text-muted-2"><?= number_format($alert, 2) ?></td>
                <td><span class="badge-pill <?= $cls ?>"><?= $lbl ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []): ?><tr><td colspan="5"><?= View::partial('components.empty', ['message' => 'No products found']) ?></td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
