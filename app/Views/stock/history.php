<?php
use App\Core\View;
/** @var array $logs */
$typeBadge = static function (string $t): string {
    return match ($t) {
        'purchase', 'transfer_in', 'adjustment', 'return_in', 'opening' => 'badge-success',
        'sale', 'transfer_out', 'return_out', 'damage'                  => 'badge-danger',
        default                                                          => 'badge-muted',
    };
};
$direction = static function (float $q): string {
    return $q >= 0 ? 'Stock In' : 'Stock Out';
};
$imeiOf = static function (array $l): string {
    if (!empty($l['imei_codes'])) {
        return (string) $l['imei_codes'];
    }
    $note = (string) ($l['note'] ?? '');
    if (preg_match('/IMEI\/Serial:\s*(.+)$/i', $note, $m)) {
        return trim($m[1]);
    }
    return '';
};
$noteClean = static function (array $l): string {
    $note = (string) ($l['note'] ?? '');
    $note = preg_replace('/\s*·\s*IMEI\/Serial:\s*.+$/i', '', $note) ?? $note;
    return trim($note);
};
?>
<?= View::partial('components.page_head', ['title' => 'Stock History', 'subtitle' => 'Every stock movement, audited']) ?>
<div class="card"><div class="table-wrap"><table class="nubia">
    <thead>
        <tr>
            <th>Date</th>
            <th>Product</th>
            <th>Warehouse</th>
            <th>Type</th>
            <th>In / Out</th>
            <th class="text-end">Qty</th>
            <th>IMEI / Serial</th>
            <th>Note</th>
            <th>By</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($logs as $l): $q = (float) $l['quantity']; $imei = $imeiOf($l); ?>
        <tr>
            <td class="text-muted-2" style="font-size:.82rem;"><?= date('M j, g:i A', strtotime($l['created_at'])) ?></td>
            <td class="fw-800"><?= e($l['product_name']) ?></td>
            <td><?= e($l['warehouse_name'] ?? '—') ?></td>
            <td><span class="badge-pill <?= $typeBadge((string) $l['type']) ?>"><?= e(str_replace('_', ' ', (string) $l['type'])) ?></span></td>
            <td>
                <span class="badge-pill <?= $q >= 0 ? 'badge-success' : 'badge-danger' ?>"><?= $direction($q) ?></span>
            </td>
            <td class="text-end fw-800" style="color:<?= $q < 0 ? 'var(--danger)' : 'var(--success)' ?>;"><?= ($q >= 0 ? '+' : '') . number_format($q, 2) ?></td>
            <td style="font-family:ui-monospace,monospace;font-size:.78rem;max-width:260px;word-break:break-word;">
                <?= $imei !== '' ? e($imei) : '<span class="text-muted-2">—</span>' ?>
            </td>
            <td class="text-muted-2" style="font-size:.82rem;"><?= e($noteClean($l) ?: '—') ?></td>
            <td class="text-muted-2" style="font-size:.82rem;"><?= e($l['user_name'] ?? 'System') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($logs === []): ?><tr><td colspan="9"><?= View::partial('components.empty', ['message' => 'No stock movements yet']) ?></td></tr><?php endif; ?>
    </tbody>
</table></div></div>
