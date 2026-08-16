<?php
use App\Core\View;
/** @var array $product @var array $stockRows @var array $logs */
?>
<?= View::partial('components.page_head', [
    'title'    => $product['name'],
    'subtitle' => 'SKU: ' . e($product['sku']),
    'extraActions' => (can('products.edit') ? '<a href="' . url('products/' . $product['id'] . '/edit') . '" class="btn btn-soft"><span class="material-symbols-rounded">edit</span> Edit</a>' : ''),
]) ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="avatar lg mx-auto mb-3"><?= e(strtoupper(substr($product['name'], 0, 1))) ?></div>
                <h5 class="fw-800 mb-1"><?= e($product['name']) ?></h5>
                <p class="text-muted-2 mb-3"><?= e($product['category_name'] ?? 'Uncategorized') ?> · <?= e($product['brand_name'] ?? 'No brand') ?></p>
                <div class="row g-2 text-center">
                    <?php if (can('costs.view')): ?><div class="col-6"><div class="glass p-2"><div class="text-muted-2" style="font-size:.72rem;">Cost <span class="inc-vat">inc.vat</span></div><div class="fw-800"><?= money($product['cost_price']) ?></div></div></div><?php endif; ?>
                    <div class="col-6"><div class="glass p-2"><div class="text-muted-2" style="font-size:.72rem;">Price <span class="inc-vat">inc.vat</span></div><div class="fw-800"><?= money($product['selling_price']) ?></div></div></div>
                    <div class="col-6"><div class="glass p-2"><div class="text-muted-2" style="font-size:.72rem;">Wholesale <span class="inc-vat">inc.vat</span></div><div class="fw-800"><?= money($product['wholesale_price']) ?></div></div></div>
                    <div class="col-6"><div class="glass p-2"><div class="text-muted-2" style="font-size:.72rem;">In Stock</div><div class="fw-800"><?= rtrim(rtrim(number_format((float) $product['stock'], 2), '0'), '.') ?></div></div></div>
                </div>
            </div>
        </div>
        <?php if (can('products.labels')): ?><div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Barcode &amp; QR</span>
            </div>
            <div class="card-body text-center">
                <img src="<?= url('products/' . $product['id'] . '/barcode') ?>" alt="barcode" style="max-width:100%;background:#fff;padding:8px;border-radius:10px;">
                <div class="text-muted-2 mt-1" style="font-size:.78rem;"><?= e($product['barcode'] ?: $product['sku']) ?></div>
                <div class="mt-3"><img src="<?= url('products/' . $product['id'] . '/qrcode') ?>" alt="qr" width="150" style="background:#fff;padding:8px;border-radius:10px;"></div>
                <div class="d-grid gap-2 mt-3">
                    <a href="<?= url('products/' . $product['id'] . '/labels?type=barcode&copies=1') ?>" target="_blank" class="btn btn-soft btn-sm">
                        <span class="material-symbols-rounded" style="font-size:18px;">barcode</span> Print Barcode
                    </a>
                    <a href="<?= url('products/' . $product['id'] . '/labels?type=qr&copies=1') ?>" target="_blank" class="btn btn-soft btn-sm">
                        <span class="material-symbols-rounded" style="font-size:18px;">qr_code_2</span> Print QR
                    </a>
                    <a href="<?= url('products/' . $product['id'] . '/labels?type=both&copies=1') ?>" target="_blank" class="btn btn-brand btn-sm">
                        <span class="material-symbols-rounded" style="font-size:18px;">print</span> Print Both
                    </a>
                </div>
            </div>
        </div><?php endif; ?>
    </div>
    <div class="col-lg-8">
        <?php if (can('stock.view')): ?><div class="card mb-3">
            <div class="card-header">Stock by Warehouse</div>
            <div class="table-wrap">
                <table class="nubia">
                    <thead><tr><th>Warehouse</th><th>Quantity</th><th>Batch</th><th>Expiry</th></tr></thead>
                    <tbody>
                    <?php if (!$stockRows): ?><tr><td colspan="4" class="text-muted-2 text-center py-3">No stock recorded.</td></tr><?php endif; ?>
                    <?php foreach ($stockRows as $s): ?>
                        <tr><td><?= e($s['warehouse_name']) ?></td><td class="fw-800"><?= rtrim(rtrim(number_format((float) $s['quantity'], 2), '0'), '.') ?></td><td><?= e($s['batch_no'] ?? '—') ?></td><td><?= e($s['expiry_date'] ?? '—') ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><?php endif; ?>

        <?php
        /** @var array $serials @var array $serialsMeta @var int $serialsAvail @var array $logsMeta */
        $serials = $serials ?? [];
        $serialsMeta = $serialsMeta ?? ['total' => 0, 'page' => 1, 'per_page' => 5, 'last_page' => 1];
        $logsMeta = $logsMeta ?? ['total' => 0, 'page' => 1, 'per_page' => 5, 'last_page' => 1];
        $avail = (int) ($serialsAvail ?? 0);
        $serialsBase = url('products/' . $product['id']) . '?logs_page=' . (int) $logsMeta['page'];
        $logsBase = url('products/' . $product['id']) . '?serials_page=' . (int) $serialsMeta['page'];
        ?>
        <?php if (can('products.serials.view') && (!empty($product['has_imei']) || !empty($product['has_serial']))): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>IMEI / Serial Units <span class="badge-pill badge-success ms-1"><?= $avail ?> available</span></span>
                <?php if (can('products.labels') && ($serialsMeta['total'] ?? 0) > 0): ?>
                    <a href="<?= url('products/' . $product['id'] . '/labels?type=both&copies=1') ?>"
                       target="_blank" class="btn btn-soft btn-sm">
                        <span class="material-symbols-rounded">print</span> Print all IMEI labels
                    </a>
                <?php endif; ?>
            </div>
            <?php if (can('products.serials.manage')): ?>
            <div class="card-body border-bottom">
                <form method="POST" action="<?= url('products/' . $product['id'] . '/serials') ?>" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-md-8">
                        <label class="form-label">Add IMEIs</label>
                        <textarea name="imei_list" class="form-control" rows="3" required data-imei-chips data-imei-placeholder="Scan or type an IMEI, then press Enter"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select mb-2">
                            <?php foreach (($warehouses ?? []) as $w): ?>
                                <option value="<?= (int) $w['id'] ?>" <?= !empty($w['is_default']) ? 'selected' : '' ?>><?= e($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-brand w-100" type="submit"><span class="material-symbols-rounded">add</span> Add to stock</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            <div class="table-wrap">
                <table class="nubia">
                    <thead><tr><th>IMEI / Serial</th><th>Warehouse</th><th>Status</th><th>Added</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php if (!$serials): ?><tr><td colspan="5" class="text-muted-2 text-center py-3">No IMEI/serial units yet. Add them above or via Purchase.</td></tr><?php endif; ?>
                    <?php foreach ($serials as $row): ?>
                        <?php
                        $code = $row['imei'] ?: ($row['serial_no'] ?? '');
                        $st = (string) ($row['status'] ?? 'available');
                        $badge = match ($st) {
                            'available' => 'badge-success',
                            'sold' => 'badge-info',
                            'returned' => 'badge-warning',
                            'damaged' => 'badge-danger',
                            default => 'badge-info',
                        };
                        ?>
                        <?php $isTemp = str_starts_with($code, 'TEMP-'); ?>
                        <tr>
                            <td class="fw-800" style="font-family:ui-monospace,monospace;">
                                <?php if (can('products.serials.manage') && $st === 'available'): ?>
                                    <form method="POST" action="<?= url('products/' . $product['id'] . '/serials/' . $row['id']) ?>"
                                          class="d-flex gap-1 align-items-center">
                                        <?= csrf_field() ?>
                                        <input type="text" name="code" value="<?= e($code) ?>" required
                                               class="form-control form-control-sm <?= $isTemp ? 'is-placeholder-code' : '' ?>"
                                               style="font-family:ui-monospace,monospace;max-width:190px;">
                                        <button type="submit" class="btn btn-soft btn-sm" title="Save IMEI">
                                            <span class="material-symbols-rounded">check</span>
                                        </button>
                                    </form>
                                    <?php if ($isTemp): ?>
                                        <span class="badge-pill badge-warning mt-1" style="font-size:.62rem;">Placeholder — replace</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= e($code) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= e($row['warehouse_name'] ?? '—') ?></td>
                            <td><span class="badge-pill <?= $badge ?>"><?= e(ucfirst($st)) ?></span></td>
                            <td class="text-muted-2"><?= e(date('M j, Y', strtotime((string) $row['created_at']))) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?php if (can('products.labels')): ?><a href="<?= url('products/' . $product['id'] . '/serials/' . $row['id'] . '/label?type=both') ?>"
                                       target="_blank" class="btn btn-soft btn-sm">
                                        <span class="material-symbols-rounded">qr_code_2</span> Print
                                    </a><?php endif; ?>
                                    <?php if (can('products.serials.manage') && $st === 'available'): ?>
                                        <form method="POST" action="<?= url('products/' . $product['id'] . '/serials/' . $row['id'] . '/delete') ?>"
                                              onsubmit="return confirm('Remove <?= e($code) ?>? Stock will drop by 1.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger-soft btn-sm" title="Remove unit">
                                                <span class="material-symbols-rounded">delete</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= View::partial('components.pagination', [
                'meta'      => $serialsMeta,
                'baseUrl'   => $serialsBase,
                'pageParam' => 'serials_page',
            ]) ?>
        </div>
        <?php endif; ?>

        <?php if (can('stock.history')): ?><div class="card">
            <div class="card-header">Stock Movement History</div>
            <div class="table-wrap">
                <table class="nubia">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>In / Out</th>
                            <th>Change</th>
                            <th>Balance</th>
                            <th>IMEI / Serial</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$logs): ?><tr><td colspan="8" class="text-muted-2 text-center py-3">No movements yet.</td></tr><?php endif; ?>
                    <?php foreach ($logs as $l): ?>
                        <?php
                        $q = (float) $l['quantity'];
                        $imei = (string) ($l['imei_codes'] ?? '');
                        if ($imei === '' && !empty($l['note']) && preg_match('/IMEI\/Serial:\s*(.+)$/i', (string) $l['note'], $m)) {
                            $imei = trim($m[1]);
                        }
                        $note = (string) ($l['note'] ?? '');
                        $note = trim((string) preg_replace('/\s*·\s*IMEI\/Serial:\s*.+$/i', '', $note));
                        ?>
                        <tr>
                            <td><?= date('M j, Y g:i A', strtotime($l['created_at'])) ?></td>
                            <td class="fw-800"><?= e($product['name']) ?></td>
                            <td><span class="badge-pill badge-info"><?= e(ucwords(str_replace('_', ' ', $l['type']))) ?></span></td>
                            <td><span class="badge-pill <?= $q >= 0 ? 'badge-success' : 'badge-danger' ?>"><?= $q >= 0 ? 'Stock In' : 'Stock Out' ?></span></td>
                            <td class="fw-800 <?= $q >= 0 ? 'text-success' : 'text-danger' ?>"><?= $q >= 0 ? '+' : '' ?><?= rtrim(rtrim(number_format($q, 2), '0'), '.') ?></td>
                            <td><?= rtrim(rtrim(number_format((float) $l['balance_after'], 2), '0'), '.') ?></td>
                            <td style="font-family:ui-monospace,monospace;font-size:.78rem;max-width:240px;word-break:break-word;">
                                <?= $imei !== '' ? e($imei) : '<span class="text-muted-2">—</span>' ?>
                            </td>
                            <td class="text-muted-2"><?= e($note !== '' ? $note : '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= View::partial('components.pagination', [
                'meta'      => $logsMeta,
                'baseUrl'   => $logsBase,
                'pageParam' => 'logs_page',
            ]) ?>
        </div><?php endif; ?>
    </div>
</div>
