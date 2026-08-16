<?php
use App\Core\View;
/** @var array $customers @var array $warehouses */
?>
<?= View::partial('components.page_head', ['title' => 'New Sale', 'subtitle' => 'Create a retail or wholesale sale']) ?>

<form method="POST" action="<?= url('sales') ?>" id="saleForm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-header">Sale Info</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Customer</label>
                        <select name="customer_id" id="fCustomer" class="form-select"><option value="">Walk-in Customer</option>
                            <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select"><?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Type</label><select name="type" class="form-select" id="saleType"><option value="retail">Retail</option><option value="wholesale">Wholesale</option></select></div>
                    <div class="col-md-2"><label class="form-label">Date</label><input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                </div>
            </div></div>

            <div class="card"><div class="card-header">Products</div><div class="card-body">
                <div class="position-relative mb-3">
                    <input type="text" id="prodSearch" class="form-control" placeholder="Scan barcode or search product…" autocomplete="off" autofocus>
                    <div class="search-results" id="prodResults" style="position:absolute;"></div>
                </div>
                <div class="table-wrap"><table class="nubia">
                    <thead><tr><th>Product</th><th style="width:90px;">Qty</th><th style="width:120px;">Price</th><th>IMEI</th><th style="width:110px;">Subtotal</th><th></th></tr></thead>
                    <tbody id="itemsBody"><tr id="noItems"><td colspan="6" class="text-center text-muted-2 py-3">No products added yet.</td></tr></tbody>
                </table></div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-header">Payment Summary</div><div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Subtotal</span><span class="fw-800" id="sumSubtotal"><?= money(0) ?></span></div>
                <div class="row g-2 mb-2">
                    <div class="col-7"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" id="fDiscount" class="form-control" value="0"></div>
                    <div class="col-5"><label class="form-label">Type</label><select name="discount_type" id="fDiscType" class="form-select"><option value="fixed">Fixed</option><option value="percent">%</option></select></div>
                    <div class="col-6"><label class="form-label">Tax</label><input type="number" step="0.01" name="tax" id="fTax" class="form-control" value="0"></div>
                    <div class="col-6"><label class="form-label">Shipping</label><input type="number" step="0.01" name="shipping" id="fShipping" class="form-control" value="0"></div>
                </div>
                <hr class="divider">
                <div class="d-flex justify-content-between mb-1"><span>Total</span><span class="fw-800" style="font-size:1.3rem;color:var(--brand);" id="sumTotal"><?= money(0) ?></span></div>
                <div class="row g-2 mt-1">
                    <div class="col-6"><label class="form-label">Paid</label><input type="number" step="0.01" name="paid" id="fPaid" class="form-control" value="0"></div>
                    <div class="col-6"><label class="form-label">Method</label><select name="payment_method" id="fMethod" class="form-select"><?php foreach (payment_methods(true) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="d-flex justify-content-between mt-2"><span class="text-muted-2">Due</span><span class="fw-800 text-danger" id="sumDue"><?= money(0) ?></span></div>
                <p class="text-muted-2 mb-0 mt-2" style="font-size:.75rem;">Due/credit requires a registered customer (not Walk-in).</p>
            </div></div>
            <div class="d-grid gap-2"><button class="btn btn-brand py-2" type="submit"><span class="material-symbols-rounded">check_circle</span> Complete Sale</button><a href="<?= url('sales') ?>" class="btn btn-soft">Cancel</a></div>
        </div>
    </div>
</form>

<?php $pageScript = <<<'JS'
(function(){
    let items = [];
    const body = document.getElementById('itemsBody');
    const search = document.getElementById('prodSearch');
    const results = document.getElementById('prodResults');
    const saleType = document.getElementById('saleType');
    let timer;

    search.addEventListener('input', function(){
        clearTimeout(timer);
        const q = this.value.trim();
        if(q.length < 1){ results.style.display='none'; return; }
        timer = setTimeout(async ()=>{
            const {data} = await Nubia.ajax('/api/products/search?q=' + encodeURIComponent(q));
            const list = data.results||[];
            // Barcode exact match auto-add.
            if(list.length===1){ /* keep dropdown but allow enter */ }
            results.innerHTML = list.map(p =>
                `<a href="#" data-p='${JSON.stringify(p).replace(/'/g,"&#39;")}'><strong>${p.name}</strong> <span class="text-muted-2" style="font-size:.8rem">${p.sku} · ${Nubia.fmtMoney(p.selling_price)} · Stock ${p.stock}${Number(p.has_imei)||Number(p.has_serial)?' · IMEI':''}</span></a>`
            ).join('') || '<div class="p-2 text-muted-2">No products</div>';
            results.style.display='block';
            results.querySelectorAll('a').forEach(a => a.addEventListener('click', e=>{ e.preventDefault(); addItem(JSON.parse(a.getAttribute('data-p'))); results.style.display='none'; search.value=''; search.focus(); }));
        }, 200);
    });
    document.addEventListener('click', e=>{ if(!e.target.closest('.position-relative')) results.style.display='none'; });

    function priceFor(p){ return saleType.value==='wholesale' && parseFloat(p.wholesale_price)>0 ? parseFloat(p.wholesale_price) : parseFloat(p.selling_price); }
    function addItem(p){
        const tracks = Number(p.has_imei)===1 || Number(p.has_serial)===1;
        const matched = p.matched_imei || '';
        items.push({ product_id:p.id, name:p.name, quantity:1, unit_price:priceFor(p), has_imei:tracks, imei_list: matched });
        render();
    }
    window.removeItem = (i)=>{ items.splice(i,1); render(); };
    window.upd = (i,f,v)=>{ items[i][f]=parseFloat(v)||0; render(); };
    window.updImei = (i,v)=>{ items[i].imei_list=v; };

    function render(){
        body.innerHTML = items.length ? items.map((it,idx)=>`
            <tr>
                <td>${it.name}${it.has_imei?' <span class="badge-pill badge-info">IMEI</span>':''}<input type="hidden" name="product_id[]" value="${it.product_id}"></td>
                <td><input type="number" min="1" step="1" name="quantity[]" class="form-control form-control-sm" value="${it.quantity}" oninput="upd(${idx},'quantity',this.value)"></td>
                <td><input type="number" min="0" step="0.01" name="unit_price[]" class="form-control form-control-sm" value="${it.unit_price}" oninput="upd(${idx},'unit_price',this.value)"></td>
                <td>${it.has_imei
                    ? `<textarea name="imei_list[]" class="form-control form-control-sm" rows="2" data-imei-chips data-imei-expected="${it.quantity}" data-imei-placeholder="Scan or type, then Enter" oninput="updImei(${idx},this.value)">${it.imei_list||''}</textarea>`
                    : `<input type="hidden" name="imei_list[]" value=""><span class="text-muted-2">—</span>`}</td>
                <td class="fw-800">${Nubia.fmtMoney(it.quantity*it.unit_price)}</td>
                <td><button type="button" class="icon-btn" style="width:32px;height:32px;" onclick="removeItem(${idx})"><span class="material-symbols-rounded" style="font-size:17px;color:var(--danger)">close</span></button></td>
            </tr>`).join('') : '<tr id="noItems"><td colspan="6" class="text-center text-muted-2 py-3">No products added yet.</td></tr>';
        totals();
    }
    function totals(){
        const sub = items.reduce((s,i)=>s+i.quantity*i.unit_price,0);
        const dVal = parseFloat(document.getElementById('fDiscount').value)||0;
        const dType = document.getElementById('fDiscType').value;
        const disc = dType==='percent'? sub*dVal/100 : dVal;
        const tax = parseFloat(document.getElementById('fTax').value)||0;
        const ship = parseFloat(document.getElementById('fShipping').value)||0;
        const total = Math.max(0, sub - disc + tax + ship);
        const paid = parseFloat(document.getElementById('fPaid').value)||0;
        document.getElementById('sumSubtotal').textContent = Nubia.fmtMoney(sub);
        document.getElementById('sumTotal').textContent = Nubia.fmtMoney(total);
        document.getElementById('sumDue').textContent = Nubia.fmtMoney(Math.max(0,total-paid));
    }
    ['fDiscount','fDiscType','fTax','fShipping','fPaid'].forEach(id=>document.getElementById(id).addEventListener('input',totals));
    saleType.addEventListener('change', ()=>{ items=[]; render(); });
    document.getElementById('saleForm').addEventListener('submit', e=>{
        if(!items.length){ e.preventDefault(); Nubia.toast('Add at least one product.','error'); return; }
        for (const it of items) {
            if (!it.has_imei) continue;
            const lines = (it.imei_list||'').split(/[\s,;]+/).map(s=>s.trim()).filter(Boolean);
            if (lines.length !== Math.round(it.quantity)) {
                e.preventDefault();
                Nubia.toast(it.name + ': enter exactly ' + Math.round(it.quantity) + ' IMEI(s).', 'error');
                return;
            }
        }
        const sub = items.reduce((s,i)=>s+i.quantity*i.unit_price,0);
        const dVal = parseFloat(document.getElementById('fDiscount').value)||0;
        const dType = document.getElementById('fDiscType').value;
        const disc = dType==='percent'? sub*dVal/100 : dVal;
        const tax = parseFloat(document.getElementById('fTax').value)||0;
        const ship = parseFloat(document.getElementById('fShipping').value)||0;
        const total = Math.max(0, sub - disc + tax + ship);
        const paid = parseFloat(document.getElementById('fPaid').value)||0;
        const due = Math.max(0, total - paid);
        const customerId = document.getElementById('fCustomer').value;
        const method = document.getElementById('fMethod').value;
        if ((due > 0 || method === 'credit') && !customerId) {
            e.preventDefault();
            Nubia.toast('Select a registered customer for due/credit sales. Walk-in must pay in full.', 'error');
            document.getElementById('fCustomer').focus();
        }
    });
})();
JS; ?>
