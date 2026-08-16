<?php
/** @var array $txn */
$revenue = (float) $txn['selling_price'] * (float) $txn['quantity'];
?>
<style>
    body{background:#f1f5f9;}
    .noprint{text-align:center;margin:16px;}
    @media print{.noprint{display:none;} body{background:#fff;}}
</style>
<div class="noprint"><button onclick="window.print()" style="padding:10px 22px;border:none;border-radius:10px;background:#dc2626;color:#fff;font-weight:600;cursor:pointer;">Print</button></div>
<div style="max-width:760px;margin:24px auto;background:#fff;color:#111;border-radius:12px;padding:40px;font-family:Arial,sans-serif;box-shadow:0 10px 40px rgba(0,0,0,.1);">
    <div style="display:flex;justify-content:space-between;">
        <div><h1 style="margin:0;color:#dc2626;"><?= e(setting('business_name', 'Nubia Inventory')) ?></h1><div style="color:#64748b;"><?= e(setting('business_phone', '')) ?></div></div>
        <div style="text-align:right;"><h2 style="margin:0;">INVOICE</h2><div style="color:#64748b;"><?= e($txn['reference']) ?></div><div style="color:#64748b;"><?= date('F j, Y', strtotime($txn['created_at'])) ?></div></div>
    </div>
    <hr style="margin:20px 0;border:none;border-top:1px solid #e2e8f0;">
    <div><strong>Customer:</strong> <?= e($txn['customer_name'] ?? 'Walk-in Customer') ?> <?= $txn['customer_phone'] ? '· ' . e($txn['customer_phone']) : '' ?></div>
    <table style="width:100%;border-collapse:collapse;margin-top:20px;">
        <thead><tr style="background:#f8fafc;"><th style="text-align:left;padding:10px;border-bottom:2px solid #e2e8f0;">Product</th><th style="padding:10px;border-bottom:2px solid #e2e8f0;">Qty</th><th style="padding:10px;border-bottom:2px solid #e2e8f0;">Price</th><th style="text-align:right;padding:10px;border-bottom:2px solid #e2e8f0;">Total</th></tr></thead>
        <tbody><tr><td style="padding:10px;border-bottom:1px solid #eef2f7;"><?= e($txn['product_name']) ?></td><td style="padding:10px;text-align:center;"><?= rtrim(rtrim(number_format((float) $txn['quantity'], 2), '0'), '.') ?></td><td style="padding:10px;text-align:center;"><?= money($txn['selling_price']) ?></td><td style="padding:10px;text-align:right;"><?= money($revenue) ?></td></tr></tbody>
    </table>
    <div style="margin-left:auto;width:260px;margin-top:16px;">
        <div style="display:flex;justify-content:space-between;padding:6px 0;font-weight:800;font-size:1.15rem;border-top:2px solid #e2e8f0;"><span>Total</span><span><?= money($revenue) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:4px 0;"><span style="color:#64748b;">Paid</span><span><?= money($txn['paid']) ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:4px 0;"><span style="color:#64748b;">Due</span><span><?= money($txn['due']) ?></span></div>
    </div>
    <p style="margin-top:40px;text-align:center;color:#94a3b8;font-size:.8rem;">Thank you! · Nubia Inventory Direct Buy &amp; Sell</p>
</div>
