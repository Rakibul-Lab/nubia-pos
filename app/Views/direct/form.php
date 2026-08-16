<?php
use App\Core\View;
/** @var array $suppliers @var array $customers @var array $warehouses */
?>
<?= View::partial('components.page_head', [
    'title'    => 'Direct Buy & Sell',
    'subtitle' => 'Fulfil a customer request for an out-of-stock product without losing the sale',
]) ?>

<div class="alert d-flex align-items-center gap-3 mb-3" style="background:linear-gradient(135deg,rgba(220,38,38,.10),rgba(10,10,10,.06));border:1px solid var(--border);border-radius:16px;">
    <span class="material-symbols-rounded" style="font-size:32px;color:var(--brand);">bolt</span>
    <div>
        <div class="fw-800">How it works</div>
        <div class="text-muted-2" style="font-size:.85rem;">Buy from a supplier &amp; sell to the customer instantly. We generate a purchase record, a sales invoice, and calculate your profit automatically.</div>
    </div>
</div>

<form method="POST" action="<?= url('direct-buy-sell') ?>" id="dbsForm">
    <?= csrf_field() ?>
    <div class="row g-3 align-items-start">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-header"><span class="material-symbols-rounded">inventory_2</span> Product &amp; Supplier</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Product Name *</label><input type="text" name="product_name" class="form-control" placeholder="e.g. Sony WH-1000XM5 Headphone" required></div>
                    <div class="col-md-4"><label class="form-label">Quantity *</label><input type="number" step="1" min="1" name="quantity" id="d_qty" class="form-control" value="1" required></div>
                    <div class="col-md-6"><label class="form-label">Supplier Shop</label>
                        <select name="supplier_id" class="form-select"><option value="">— Select existing —</option>
                            <?php foreach ($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">or Supplier Name (manual)</label><input type="text" name="supplier_name" class="form-control" placeholder="Walk-in supplier"></div>
                </div>
            </div></div>

            <div class="card mb-3"><div class="card-header"><span class="material-symbols-rounded">payments</span> Cost Breakdown</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Supplier Purchase Price *</label><div class="input-group"><span class="input-group-text"><?= e(setting('currency_symbol', 'Tk')) ?></span><input type="number" step="0.01" name="purchase_price" id="d_purchase" class="form-control" value="0" required></div></div>
                    <div class="col-md-4"><label class="form-label">Transportation Cost</label><div class="input-group"><span class="input-group-text"><?= e(setting('currency_symbol', 'Tk')) ?></span><input type="number" step="0.01" name="transportation_cost" id="d_transport" class="form-control" value="0"></div></div>
                    <div class="col-md-4"><label class="form-label">Additional Cost</label><div class="input-group"><span class="input-group-text"><?= e(setting('currency_symbol', 'Tk')) ?></span><input type="number" step="0.01" name="additional_cost" id="d_additional" class="form-control" value="0"></div></div>
                    <div class="col-md-6"><label class="form-label">Selling Price (per unit) *</label><div class="input-group"><span class="input-group-text"><?= e(setting('currency_symbol', 'Tk')) ?></span><input type="number" step="0.01" name="selling_price" id="d_selling" class="form-control" value="0" required></div></div>
                    <div class="col-md-6"><label class="form-label">Amount Paid by Customer</label><div class="input-group"><span class="input-group-text"><?= e(setting('currency_symbol', 'Tk')) ?></span><input type="number" step="0.01" name="paid" id="d_paid" class="form-control" placeholder="Leave blank = full"></div></div>
                </div>
            </div></div>

            <div class="card"><div class="card-header"><span class="material-symbols-rounded">person</span> Customer Information</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select"><option value="">Walk-in Customer</option>
                            <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">or Customer Name</label><input type="text" name="customer_name" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="customer_phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Warehouse</label><select name="warehouse_id" class="form-select"><?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Payment Method</label><select name="payment_method" class="form-select"><?php foreach (payment_methods(true) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
                </div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <aside class="dbs-sidebar card">
                <div class="card-header"><?= can('profit.view') || can('costs.view') ? 'Profit Calculation' : 'Sale Summary' ?></div>
                <div class="card-body dbs-sidebar-body">
                    <?php if (can('profit.view')): ?>
                    <div class="text-center mb-3">
                        <div class="text-muted-2" style="font-size:.8rem;">Estimated Profit</div>
                        <div class="fw-800 dbs-profit" id="d_profit"><?= money(0) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (can('revenue.view')): ?>
                    <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Total Revenue</span><span id="d_revenue" class="fw-800"><?= money(0) ?></span></div>
                    <?php endif; ?>
                    <?php if (can('costs.view')): ?>
                    <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Total Cost</span><span id="d_totalcost"><?= money(0) ?></span></div>
                    <?php endif; ?>
                    <?php if (can('profit.view')): ?>
                    <hr class="divider">
                    <div class="d-flex justify-content-between align-items-center py-1"><span class="text-muted-2">Margin</span><span id="d_margin" class="badge-pill badge-success">0%</span></div>
                    <?php endif; ?>

                    <label class="dbs-inv-toggle" for="d_addinv">
                        <input type="checkbox" name="add_to_inventory" value="1" id="d_addinv">
                        <span class="dbs-inv-switch" aria-hidden="true"></span>
                        <span class="dbs-inv-copy">
                            <strong>Add purchased product to inventory</strong>
                            <small>If off, only purchase and sale are recorded — no stock change.</small>
                        </span>
                    </label>
                </div>
                <div class="dbs-sidebar-actions">
                    <button class="btn btn-brand w-100 py-2" type="submit"><span class="material-symbols-rounded">bolt</span> Complete Transaction</button>
                    <a href="<?= url('direct-buy-sell') ?>" class="btn btn-soft w-100">Cancel</a>
                </div>
            </aside>
        </div>
    </div>
</form>

<?php $pageScript = <<<JS
(function(){
    const ids = ['d_qty','d_purchase','d_transport','d_additional','d_selling','d_paid'];
    const showCost = <?= can('costs.view') ? 'true' : 'false' ?>;
    const showProfit = <?= can('profit.view') ? 'true' : 'false' ?>;
    const showRevenue = <?= can('revenue.view') ? 'true' : 'false' ?>;
    function num(id){ return parseFloat(document.getElementById(id).value)||0; }
    function calc(){
        const qty = Math.max(1, num('d_qty'));
        const cost = (num('d_purchase') + num('d_transport') + num('d_additional')) * qty;
        const revenue = num('d_selling') * qty;
        const profit = revenue - cost;
        const costEl = document.getElementById('d_totalcost');
        if (costEl && showCost) costEl.textContent = Nubia.fmtMoney(cost);
        const revenueEl = document.getElementById('d_revenue');
        if (revenueEl && showRevenue) revenueEl.textContent = Nubia.fmtMoney(revenue);
        const profitEl = document.getElementById('d_profit');
        if (profitEl && showProfit) profitEl.textContent = Nubia.fmtMoney(profit);
        const m = document.getElementById('d_margin');
        if (m && showProfit) {
            const margin = revenue>0 ? (profit/revenue*100) : 0;
            m.textContent = margin.toFixed(1)+'%';
            m.className = 'badge-pill ' + (profit>=0 ? 'badge-success' : 'badge-danger');
        }
    }
    ids.forEach(id => document.getElementById(id).addEventListener('input', calc));
    calc();
})();
JS; ?>
