<?php
/** @var array $sale @var array $items @var bool|null $forPdf @var array|null $exchangeContext */
$forPdf = $forPdf ?? false;
$exchangeContext = $exchangeContext ?? null;
$isExchange = !empty($exchangeContext['is_exchange']);
?>
<?php if (!$forPdf): ?>
<style>
    body{background:#f1f5f9;}
    .noprint{text-align:center;margin:16px;}
    @media print{.noprint{display:none;} body{background:#fff;}}
</style>
<div class="noprint">
    <button onclick="window.print()" style="padding:10px 22px;border:none;border-radius:10px;background:#dc2626;color:#fff;font-weight:600;cursor:pointer;">Print Invoice</button>
</div>
<?php endif; ?>
<div style="max-width:800px;margin:24px auto;background:#fff;color:#111;border-radius:12px;padding:40px;font-family:Arial,sans-serif;<?= $forPdf ? '' : 'box-shadow:0 10px 40px rgba(0,0,0,.1);' ?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <h1 style="font-size:1.6rem;margin:0;color:#dc2626;"><?= e(setting('business_name', 'Nubia Inventory')) ?></h1>
            <div style="color:#64748b;"><?= e(setting('business_address', '')) ?></div>
            <div style="color:#64748b;"><?= e(setting('business_phone', '')) ?></div>
        </div>
        <div style="text-align:right;">
            <h2 style="margin:0;"><?= $isExchange ? 'EXCHANGE INVOICE' : 'INVOICE' ?></h2>
            <div style="color:#64748b;"><?= e($sale['invoice_no']) ?></div>
            <div style="color:#64748b;"><?= date('F j, Y', strtotime($sale['sale_date'])) ?></div>
            <?php if ($isExchange): ?>
                <div style="margin-top:8px;display:inline-block;padding:4px 10px;border-radius:999px;background:#fef2f2;color:#b91c1c;font-size:.75rem;font-weight:700;">
                    EXCHANGE<?= !empty($exchangeContext['exchange_ref']) ? ' · ' . e($exchangeContext['exchange_ref']) : '' ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isExchange): ?>
        <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:.9rem;">
            <strong>This is an exchange invoice.</strong>
            Replacement items for original sale
            <strong><?= e($exchangeContext['original_invoice'] ?? '') ?></strong>.
            <?php if (!empty($sale['note'])): ?><br><span style="color:#c2410c;"><?= e($sale['note']) ?></span><?php endif; ?>
        </div>
    <?php endif; ?>

    <hr style="margin:20px 0;border:none;border-top:1px solid #e2e8f0;">
    <div style="display:flex;justify-content:space-between;">
        <div><strong>Bill To:</strong><br><?= e($sale['customer_name'] ?? 'Walk-in Customer') ?><br><span style="color:#64748b;"><?= e($sale['customer_phone'] ?? '') ?></span></div>
        <div style="text-align:right;"><strong>Payment:</strong> <?= e(payment_method_label($sale['payment_method'])) ?><br><strong>Status:</strong> <?= ucfirst($sale['payment_status']) ?></div>
    </div>
    <table style="width:100%;border-collapse:collapse;margin-top:20px;">
        <thead><tr style="background:#f8fafc;">
            <th style="text-align:left;padding:10px;border-bottom:2px solid #e2e8f0;font-size:.75rem;color:#64748b;">PRODUCT</th>
            <th style="padding:10px;border-bottom:2px solid #e2e8f0;font-size:.75rem;color:#64748b;">QTY</th>
            <th style="padding:10px;border-bottom:2px solid #e2e8f0;font-size:.75rem;color:#64748b;">PRICE</th>
            <th style="text-align:right;padding:10px;border-bottom:2px solid #e2e8f0;font-size:.75rem;color:#64748b;">TOTAL</th>
        </tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td style="padding:10px;border-bottom:1px solid #eef2f7;"><?= e($it['product_name']) ?><br><span style="color:#94a3b8;font-size:.8rem;"><?= e($it['sku']) ?></span><?php if (!empty($it['imeis'])): ?><br><span style="color:#475569;font-size:.75rem;font-family:ui-monospace,monospace;">IMEI: <?= e(implode(', ', $it['imeis'])) ?></span><?php endif; ?></td>
                <td style="padding:10px;border-bottom:1px solid #eef2f7;text-align:center;"><?= rtrim(rtrim(number_format((float) $it['quantity'], 2), '0'), '.') ?></td>
                <td style="padding:10px;border-bottom:1px solid #eef2f7;text-align:center;"><?= money($it['unit_price']) ?></td>
                <td style="padding:10px;border-bottom:1px solid #eef2f7;text-align:right;"><?= money($it['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div style="margin-left:auto;width:280px;margin-top:16px;">
        <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:#64748b;">Subtotal</span><span><?= money($sale['subtotal']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:#64748b;">Discount</span><span>- <?= money($sale['discount']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:#64748b;">Tax/VAT</span><span><?= money((float) $sale['tax'] + (float) $sale['vat']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:#64748b;">Shipping</span><span><?= money($sale['shipping']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:2px solid #e2e8f0;font-weight:800;font-size:1.15rem;"><span>Total</span><span><?= money($sale['total']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:#64748b;">Paid</span><span><?= money($sale['paid']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:5px 0;"><span style="color:#64748b;">Due</span><span><?= money($sale['due']) ?></span></div>
    </div>
    <?php if (!$isExchange && !empty($sale['note'])): ?>
        <p style="margin-top:24px;color:#64748b;font-size:.85rem;"><strong>Note:</strong> <?= e($sale['note']) ?></p>
    <?php endif; ?>
    <p style="margin-top:40px;text-align:center;color:#94a3b8;font-size:.8rem;">Thank you for your business! · Generated by Nubia Inventory</p>
</div>
