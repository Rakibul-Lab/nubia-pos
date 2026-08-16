<?php
use App\Core\View;
/** @var array|null $product @var array $categories @var array $brands @var array $units @var array $warehouses */
$isEdit = $product !== null;
$action = $isEdit ? url('products/' . $product['id']) : url('products');
$val = static fn (string $k, $d = '') => e((string) ($product[$k] ?? $d));
?>
<?= View::partial('components.page_head', [
    'title'    => $isEdit ? 'Edit Product' : 'New Product',
    'subtitle' => $isEdit ? $product['name'] : 'Add a product to your catalog',
]) ?>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="POST"><?php endif; ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SKU *</label>
                            <input type="text" name="sku" class="form-control" value="<?= $val('sku', 'SKU-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6))) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="<?= $val('barcode') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= (int) ($product['brand_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= $val('description') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Pricing</span>
                    <span class="text-muted-2" style="font-size:.75rem;font-weight:600;">All prices are <span class="inc-vat">inc.vat</span></span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if (can('costs.view')): ?>
                        <div class="col-md-4">
                            <label class="form-label" for="cost_price">Cost Price</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="cost_price" id="cost_price" class="form-control" value="<?= $val('cost_price', '') ?>" placeholder="0.00">
                                <span class="input-group-text">inc.vat</span>
                            </div>
                            <span class="price-inc-hint">Purchase / cost amount including VAT</span>
                        </div>
                        <?php endif; ?>
                        <div class="<?= can('costs.view') ? 'col-md-4' : 'col-md-6' ?>">
                            <label class="form-label" for="selling_price">Selling Price *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" class="form-control" value="<?= $val('selling_price', '') ?>" placeholder="0.00" required>
                                <span class="input-group-text">inc.vat</span>
                            </div>
                            <span class="price-inc-hint">Retail price charged to customer</span>
                        </div>
                        <div class="<?= can('costs.view') ? 'col-md-4' : 'col-md-6' ?>">
                            <label class="form-label" for="wholesale_price">Wholesale Price</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="wholesale_price" id="wholesale_price" class="form-control" value="<?= $val('wholesale_price', '') ?>" placeholder="0.00">
                                <span class="input-group-text">inc.vat</span>
                            </div>
                            <span class="price-inc-hint">Optional bulk / dealer price</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">Inventory</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" class="form-select">
                            <option value="">— Select —</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (int) ($product['unit_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?> (<?= e($u['short_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Low Stock Alert Qty</label>
                        <input type="number" step="0.01" name="alert_quantity" class="form-control" value="<?= $val('alert_quantity', '5') ?>">
                    </div>
                    <?php if (!$isEdit): ?>
                    <div class="mb-3" id="openingStockWrap">
                        <label class="form-label">Opening Stock</label>
                        <input type="number" step="0.01" name="opening_stock" id="opening_stock" class="form-control" value="0">
                    </div>
                    <div class="mb-3 d-none" id="imeiListWrap">
                        <label class="form-label">Opening IMEI / Serials</label>
                        <textarea name="imei_list" id="imei_list" class="form-control" rows="6" data-imei-chips data-imei-placeholder="Scan or type an IMEI, then press Enter"></textarea>
                        <div class="form-text">Each entry = 1 unit. Stock qty will match the IMEI count.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select">
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= $w['id'] ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= e($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Tracking &amp; Status</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="has_serial" value="1" id="hs" <?= ($product['has_serial'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="hs">Track Serial Number</label></div>
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="has_imei" value="1" id="hi" <?= ($product['has_imei'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="hi">Track IMEI</label></div>
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="has_expiry" value="1" id="he" <?= ($product['has_expiry'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="he">Track Expiry Date</label></div>
                    <hr class="divider">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" value="1" id="st" <?= ($product['status'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label" for="st">Active</label></div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-brand py-2"><span class="material-symbols-rounded">save</span> <?= $isEdit ? 'Update Product' : 'Save Product' ?></button>
                <a href="<?= url('products') ?>" class="btn btn-soft">Cancel</a>
            </div>
        </div>
    </div>
</form>
<?php if (!$isEdit): ?>
<?php $pageScript = <<<'JS'
(function(){
    const hi = document.getElementById('hi');
    const hs = document.getElementById('hs');
    const opening = document.getElementById('openingStockWrap');
    const imeiWrap = document.getElementById('imeiListWrap');
    function sync(){
        const track = (hi && hi.checked) || (hs && hs.checked);
        if (opening) opening.classList.toggle('d-none', !!track);
        if (imeiWrap) imeiWrap.classList.toggle('d-none', !track);
    }
    hi?.addEventListener('change', sync);
    hs?.addEventListener('change', sync);
    sync();
})();
JS; ?>
<?php endif; ?>
