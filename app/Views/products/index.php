<?php
use App\Core\View;
/** @var array $products @var array $meta @var string $keyword @var array $categories @var int|null $categoryId */

$exportQuery = array_filter([
    'q'        => $keyword !== '' ? $keyword : null,
    'category' => $categoryId ?: null,
]);
$exportBase = url('products/export') . ($exportQuery ? ('?' . http_build_query($exportQuery) . '&') : '?');
$canCosts = can('costs.view');
$colspan = $canCosts ? 8 : 7;
?>
<?= View::partial('components.page_head', [
    'title'       => 'Products',
    'subtitle'    => number_format($meta['total']) . ' products in catalog',
    'extraActions'=> can('products.export') ?
        '<a href="' . e($exportBase . 'format=excel') . '" class="btn btn-soft"><span class="material-symbols-rounded">table_view</span> Excel</a> '
        . '<a href="' . e($exportBase . 'format=pdf') . '" class="btn btn-soft"><span class="material-symbols-rounded">picture_as_pdf</span> PDF</a>' : '',
    'actionUrl'   => can('products.create') ? url('products/create') : null,
    'actionLabel' => 'Add Product',
    'actionIcon'  => 'add',
]) ?>

<div class="card mb-3">
    <div class="card-body py-3">
        <form class="row g-2 align-items-center" method="GET" action="<?= url('products') ?>">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><span class="material-symbols-rounded">search</span></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by name, SKU, barcode, IMEI or serial…" value="<?= e($keyword) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $categoryId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="warehouse" class="form-select" title="Stock shown for this warehouse">
                    <option value="">All Warehouses</option>
                    <?php foreach (($warehouses ?? []) as $w): ?>
                        <option value="<?= $w['id'] ?>" <?= ($warehouseId ?? null) === (int) $w['id'] ? 'selected' : '' ?>><?= e($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-brand">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead>
                <tr>
                    <th>Product</th><th>SKU</th><th>Category</th><?php if ($canCosts): ?><th>Cost <span class="inc-vat">inc.vat</span></th><?php endif; ?><th>Price <span class="inc-vat">inc.vat</span></th><th>Stock<?= ($warehouseId ?? null) ? ' <span class="inc-vat">this warehouse</span>' : ' <span class="inc-vat">all warehouses</span>' ?></th><th>Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$products): ?>
                <tr><td colspan="<?= $colspan ?>"><?= View::partial('components.empty', ['icon' => 'inventory_2', 'title' => 'No products found', 'text' => 'Add your first product to get started.']) ?></td></tr>
            <?php endif; ?>
            <?php foreach ($products as $p): ?>
                <?php $stock = (float) $p['stock']; $alert = (float) $p['alert_quantity']; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar sm"><?= e(strtoupper(substr($p['name'], 0, 1))) ?></div>
                            <div><div class="fw-800"><?= e($p['name']) ?></div><div class="text-muted-2" style="font-size:.75rem;"><?= e($p['brand_name'] ?? '') ?></div></div>
                        </div>
                    </td>
                    <td><span class="badge-pill badge-muted"><?= e($p['sku']) ?></span></td>
                    <td><?= e($p['category_name'] ?? '—') ?></td>
                    <?php if ($canCosts): ?><td><?= money_inc_vat($p['cost_price']) ?></td><?php endif; ?>
                    <td class="fw-800"><?= money_inc_vat($p['selling_price']) ?></td>
                    <td>
                        <span class="badge-pill <?= $stock <= 0 ? 'badge-danger' : ($stock <= $alert ? 'badge-warning' : 'badge-success') ?>">
                            <?= rtrim(rtrim(number_format($stock, 2), '0'), '.') ?> <?= e($p['unit'] ?? '') ?>
                        </span>
                    </td>
                    <td><span class="badge-pill <?= $p['status'] ? 'badge-success' : 'badge-muted' ?>"><?= $p['status'] ? 'Active' : 'Inactive' ?></span></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a href="<?= url('products/' . $p['id']) ?>" class="icon-btn" style="width:34px;height:34px;" title="View"><span class="material-symbols-rounded" style="font-size:18px;">visibility</span></a>
                            <?php if (can('products.edit')): ?>
                                <a href="<?= url('products/' . $p['id'] . '/edit') ?>" class="icon-btn" style="width:34px;height:34px;" title="Edit"><span class="material-symbols-rounded" style="font-size:18px;">edit</span></a>
                            <?php endif; ?>
                            <?php if (can('products.delete')): ?>
                                <form method="POST" action="<?= url('products/' . $p['id']) ?>" class="d-inline" id="del-<?= $p['id'] ?>">
                                    <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="icon-btn" style="width:34px;height:34px;" data-confirm-delete="Delete <?= e($p['name']) ?>?" title="Delete"><span class="material-symbols-rounded" style="font-size:18px;color:var(--danger);">delete</span></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3">
        <?= View::partial('components.pagination', [
            'meta'    => $meta,
            'baseUrl' => url('products') . '?q=' . urlencode($keyword)
                . ($categoryId ? '&category=' . $categoryId : '')
                . (($warehouseId ?? null) ? '&warehouse=' . $warehouseId : ''),
        ]) ?>
    </div>
</div>
