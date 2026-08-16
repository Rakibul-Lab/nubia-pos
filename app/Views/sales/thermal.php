<?php
/** @var array $sale @var array $items @var array|null $exchangeContext */
$inc = static fn ($amount, bool $withSymbol = false): string => money($amount, $withSymbol) . ' inc.vat';
$exchangeContext = $exchangeContext ?? null;
$isExchange = !empty($exchangeContext['is_exchange']);
?>
<style>
    * { font-family: 'Courier New', Courier, monospace; box-sizing: border-box; }
    body { background: #e2e8f0; margin: 0; padding: 16px; }
    .receipt { width: 300px; margin: 0 auto; background: #fff; padding: 16px; color: #000; font-size: 12px; }
    .center { text-align: center; }
    .receipt hr { border: none; border-top: 1px dashed #000; margin: 8px 0; }
    .receipt table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .receipt td { padding: 2px 0; vertical-align: top; }
    .receipt .label { white-space: nowrap; padding-right: 8px; }
    .receipt .value { text-align: right; white-space: nowrap; width: 1%; }
    .receipt .item-name { padding-bottom: 0; font-weight: bold; }
    .receipt .item-qty { padding-top: 0; font-size: 11px; }
    .receipt .total-row td { font-weight: bold; font-size: 14px; padding-top: 4px; }
    .noprint { text-align: center; margin-bottom: 12px; }
    @media print {
        body { background: #fff; padding: 0; }
        .noprint { display: none; }
        .receipt { width: 80mm; max-width: 80mm; padding: 4mm; }
        @page { margin: 0; size: 80mm auto; }
    }
</style>
<div class="noprint">
    <button onclick="window.print()" style="padding:8px 20px;border:none;border-radius:8px;background:#dc2626;color:#fff;cursor:pointer;">Print Receipt</button>
</div>
<div class="receipt">
    <div class="center">
        <strong style="font-size:15px;"><?= e(setting('business_name', 'Nubia Inventory')) ?></strong><br>
        <?= e(setting('business_address', '')) ?><br>
        <?= e(setting('business_phone', '')) ?>
    </div>
    <hr>
    <?php if ($isExchange): ?>
        <div class="center" style="font-weight:bold;letter-spacing:.04em;">*** EXCHANGE ***</div>
        <hr>
    <?php endif; ?>
    <table>
        <tr><td class="label"><?= $isExchange ? 'Exch Inv:' : 'Invoice:' ?></td><td class="value"><?= e($sale['invoice_no']) ?></td></tr>
        <?php if ($isExchange && !empty($exchangeContext['original_invoice'])): ?>
            <tr><td class="label">Orig Inv:</td><td class="value"><?= e($exchangeContext['original_invoice']) ?></td></tr>
        <?php endif; ?>
        <?php if ($isExchange && !empty($exchangeContext['exchange_ref'])): ?>
            <tr><td class="label">Exch Ref:</td><td class="value"><?= e($exchangeContext['exchange_ref']) ?></td></tr>
        <?php endif; ?>
        <tr><td class="label">Date:</td><td class="value"><?= date('m/d/Y H:i', strtotime($sale['created_at'])) ?></td></tr>
        <tr><td class="label">Customer:</td><td class="value"><?= e($sale['customer_name'] ?? 'Walk-in') ?></td></tr>
        <tr><td class="label">Cashier:</td><td class="value"><?= e($sale['cashier'] ?? '') ?></td></tr>
    </table>
    <hr>
    <table>
        <?php foreach ($items as $it): ?>
            <tr>
                <td colspan="2" class="item-name"><?= e($it['product_name']) ?></td>
            </tr>
            <?php if (!empty($it['imeis'])): ?>
            <tr>
                <td colspan="2" style="font-size:10px;">IMEI: <?= e(implode(', ', $it['imeis'])) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="item-qty"><?= (int) $it['quantity'] ?> x <?= $inc($it['unit_price']) ?></td>
                <td class="value"><?= $inc($it['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <hr>
    <table>
        <tr><td class="label">Subtotal</td><td class="value"><?= $inc($sale['subtotal']) ?></td></tr>
        <?php if ((float) $sale['discount'] > 0): ?>
            <tr><td class="label">Discount</td><td class="value">-<?= $inc($sale['discount']) ?></td></tr>
        <?php endif; ?>
        <tr class="total-row"><td class="label">TOTAL</td><td class="value"><?= $inc($sale['total']) ?></td></tr>
        <tr><td class="label">Paid (<?= e(payment_method_label($sale['payment_method'])) ?>)</td><td class="value"><?= $inc($sale['paid']) ?></td></tr>
        <?php if ((float) $sale['due'] > 0): ?>
            <tr><td class="label">Due</td><td class="value"><?= $inc($sale['due']) ?></td></tr>
        <?php endif; ?>
        <?php if ((float) $sale['change_amount'] > 0): ?>
            <tr><td class="label">Change</td><td class="value"><?= $inc($sale['change_amount']) ?></td></tr>
        <?php endif; ?>
    </table>
    <hr>
    <div class="center">
        <?php if ($isExchange): ?>Exchange completed<br><?php endif; ?>
        Thank you for shopping!<br>
        <span style="font-size:10px;">All prices inc.vat · <?= e(setting('currency', 'BDT')) ?></span>
    </div>
</div>
<script>window.addEventListener('load', () => { setTimeout(() => window.print(), 400); });</script>
