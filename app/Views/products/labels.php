<?php
/**
 * @var array $product
 * @var string $type
 * @var int $copies
 * @var string $code
 * @var array|null $serialUnit
 * @var array<int,array<string,mixed>> $serialUnits
 */
$showBarcode = in_array($type, ['barcode', 'both'], true);
$showQr      = in_array($type, ['qr', 'both'], true);
$serialUnit  = $serialUnit ?? null;
$serialUnits = $serialUnits ?? [];
$units = $serialUnit ? [$serialUnit] : ($serialUnits ?: [null]);

$assetUrls = static function (?array $unit) use ($product): array {
    if ($unit !== null) {
        $base = 'products/' . $product['id'] . '/serials/' . $unit['id'];
        return [url($base . '/barcode'), url($base . '/qrcode')];
    }
    return [
        url('products/' . $product['id'] . '/barcode'),
        url('products/' . $product['id'] . '/qrcode'),
    ];
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Print Labels') ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #e5e5e5;
            color: #111;
        }
        .toolbar {
            max-width: 920px;
            margin: 0 auto 16px;
            background: #fff;
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: end;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
        }
        .toolbar label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #525252;
            margin-bottom: 4px;
        }
        .toolbar select, .toolbar input {
            height: 40px;
            border: 1px solid #d4d4d4;
            border-radius: 10px;
            padding: 0 12px;
            font-size: 14px;
            min-width: 140px;
        }
        .toolbar .actions { margin-left: auto; display: flex; gap: 8px; }
        .btn {
            height: 40px;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #dc2626; color: #fff; }
        .btn-back { background: #f5f5f5; color: #111; text-decoration: none; }
        .sheet {
            max-width: 920px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }
        .label-card {
            background: #fff;
            border: 1px solid #d4d4d4;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .label-name {
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 4px;
            line-height: 1.25;
        }
        .label-meta {
            font-size: 11px;
            color: #525252;
            margin-bottom: 10px;
        }
        .label-price {
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #dc2626;
        }
        .label-barcode img {
            max-width: 100%;
            height: auto;
            background: #fff;
        }
        .label-code {
            font-size: 11px;
            letter-spacing: 0.04em;
            margin-top: 4px;
            font-family: Consolas, monospace;
        }
        .label-qr {
            margin-top: 10px;
        }
        .label-qr img {
            width: 110px;
            height: 110px;
            background: #fff;
        }
        .label-both .codes {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .label-both .label-qr img { width: 90px; height: 90px; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .sheet {
                max-width: none;
                gap: 8px;
                grid-template-columns: repeat(3, 1fr);
            }
            .label-card {
                border: 1px dashed #999;
                border-radius: 0;
                box-shadow: none;
            }
            @page { margin: 8mm; }
        }
    </style>
</head>
<body>
<div class="toolbar noprint">
    <div>
        <label for="typeSelect">Print</label>
        <select id="typeSelect">
            <option value="barcode" <?= $type === 'barcode' ? 'selected' : '' ?>>Barcode only</option>
            <option value="qr" <?= $type === 'qr' ? 'selected' : '' ?>>QR only</option>
            <option value="both" <?= $type === 'both' ? 'selected' : '' ?>>Barcode + QR</option>
        </select>
    </div>
    <div>
        <label for="copiesInput"><?= count($units) > 1 ? 'Copies per IMEI' : 'Copies' ?></label>
        <input type="number" id="copiesInput" min="1" max="50" value="<?= (int) $copies ?>">
    </div>
    <div class="actions">
        <a class="btn btn-back" href="<?= url('products/' . $product['id']) ?>">Back</a>
        <button class="btn btn-print" type="button" onclick="window.print()">Print</button>
    </div>
</div>

<div class="sheet">
    <?php foreach ($units as $unit): ?>
        <?php
        [$barcodeUrl, $qrUrl] = $assetUrls($unit);
        $labelCode = $unit !== null
            ? (string) ($unit['imei'] ?: $unit['serial_no'])
            : $code;
        ?>
        <?php for ($i = 0; $i < $copies; $i++): ?>
        <div class="label-card <?= $showBarcode && $showQr ? 'label-both' : '' ?>">
            <div class="label-name"><?= e($product['name']) ?></div>
            <div class="label-meta">SKU: <?= e($product['sku']) ?></div>
            <div class="label-price"><?= money($product['selling_price']) ?> <span style="font-size:10px;font-weight:700;color:#737373;">inc.vat</span></div>
            <div class="codes">
                <?php if ($showBarcode): ?>
                    <div class="label-barcode">
                        <img src="<?= e($barcodeUrl) ?>" alt="Barcode">
                        <div class="label-code">
                            <?php if ($unit !== null): ?>
                                IMEI/Serial: <?= e($labelCode) ?>
                            <?php else: ?>
                                <?= e($labelCode) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($showQr): ?>
                    <div class="label-qr">
                        <img src="<?= e($qrUrl) ?>" alt="QR Code">
                        <?php if ($unit !== null && !$showBarcode): ?>
                            <div class="label-code">IMEI/Serial: <?= e($labelCode) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endfor; ?>
    <?php endforeach; ?>
</div>

<script>
(function () {
    const base = <?= json_encode(
        $serialUnit
            ? url('products/' . $product['id'] . '/serials/' . $serialUnit['id'] . '/label')
            : url('products/' . $product['id'] . '/labels')
    ) ?>;
    function reload() {
        const type = document.getElementById('typeSelect').value;
        const copies = document.getElementById('copiesInput').value || 1;
        window.location.href = base + '?type=' + encodeURIComponent(type) + '&copies=' + encodeURIComponent(copies);
    }
    document.getElementById('typeSelect').addEventListener('change', reload);
    document.getElementById('copiesInput').addEventListener('change', reload);
})();
</script>
</body>
</html>
