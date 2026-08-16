<?php
use App\Core\View;
/** @var array $suppliers @var array $warehouses */
?>
<?= View::partial('components.page_head', ['title' => 'New Purchase', 'subtitle' => 'Record a stock purchase from a supplier']) ?>

<form method="POST" action="<?= url('purchases') ?>" id="purchaseForm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-header">Purchase Info</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select"><option value="">Cash Purchase</option>
                            <?php foreach ($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select"><?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                </div>
            </div></div>

            <div class="card"><div class="card-header">Products</div><div class="card-body">
                <div class="position-relative mb-3">
                    <input type="text" id="prodSearch" class="form-control" placeholder="Search product by name / SKU / barcode to add…" autocomplete="off">
                    <div class="search-results" id="prodResults" style="position:absolute;"></div>
                </div>
                <div class="table-wrap"><table class="nubia" id="itemsTable">
                    <thead><tr><th>Product</th><th style="width:90px;">Qty</th><th style="width:120px;">Unit Cost</th><th>IMEI / Serials</th><th style="width:110px;">Subtotal</th><th></th></tr></thead>
                    <tbody id="itemsBody"><tr id="noItems"><td colspan="6" class="text-center text-muted-2 py-3">No products added yet.</td></tr></tbody>
                </table></div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-header">Summary</div><div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Subtotal</span><span class="fw-800" id="sumSubtotal"><?= money(0) ?></span></div>
                <div class="row g-2 mb-2">
                    <div class="col-6"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" id="fDiscount" class="form-control" value="0"></div>
                    <div class="col-6"><label class="form-label">Tax</label><input type="number" step="0.01" name="tax" id="fTax" class="form-control" value="0"></div>
                    <div class="col-6"><label class="form-label">Shipping</label><input type="number" step="0.01" name="shipping" id="fShipping" class="form-control" value="0"></div>
                    <div class="col-6"><label class="form-label">Paid</label><input type="number" step="0.01" name="paid" id="fPaid" class="form-control" value="0"></div>
                </div>
                <hr class="divider">
                <div class="d-flex justify-content-between mb-1"><span>Total</span><span class="fw-800" style="font-size:1.2rem;" id="sumTotal"><?= money(0) ?></span></div>
                <div class="d-flex justify-content-between"><span class="text-muted-2">Due</span><span class="fw-800 text-danger" id="sumDue"><?= money(0) ?></span></div>
                <div class="mt-3"><label class="form-label">Payment Method</label><select name="payment_method" class="form-select"><?php foreach (payment_methods(false) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="mt-2"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
            </div></div>
            <div class="d-grid gap-2"><button class="btn btn-brand py-2" type="submit"><span class="material-symbols-rounded">save</span> Save Purchase</button><a href="<?= url('purchases') ?>" class="btn btn-soft">Cancel</a></div>
        </div>
    </div>
</form>

<?php $pageScript = <<<'JS'
(function(){
    let items = [];
    const body = document.getElementById('itemsBody');
    const search = document.getElementById('prodSearch');
    const results = document.getElementById('prodResults');
    let timer;

    search.addEventListener('input', function(){
        clearTimeout(timer);
        const q = this.value.trim();
        if(q.length < 1){ results.style.display='none'; return; }
        timer = setTimeout(async ()=>{
            const {data} = await Nubia.ajax('/api/products/search?q=' + encodeURIComponent(q));
            results.innerHTML = (data.results||[]).map(p =>
                `<a href="#" data-p='${JSON.stringify(p).replace(/'/g,"&#39;")}'><strong>${p.name}</strong> <span class="text-muted-2" style="font-size:.8rem">${p.sku} · Stock ${p.stock}${Number(p.has_imei)||Number(p.has_serial)?' · IMEI':''}</span></a>`
            ).join('') || '<div class="p-2 text-muted-2">No products</div>';
            results.style.display='block';
            results.querySelectorAll('a').forEach(a => a.addEventListener('click', e=>{
                e.preventDefault(); addItem(JSON.parse(a.getAttribute('data-p'))); results.style.display='none'; search.value='';
            }));
        }, 220);
    });
    document.addEventListener('click', e=>{ if(!e.target.closest('.position-relative')) results.style.display='none'; });

    function addItem(p){
        const tracks = Number(p.has_imei)===1 || Number(p.has_serial)===1;
        items.push({ product_id:p.id, name:p.name, quantity: tracks?1:1, unit_cost:parseFloat(p.cost_price)||0, has_imei:tracks, imei_list:'' });
        render();
    }
    window.removeItem = function(idx){ items.splice(idx,1); render(); };

    function render(){
        document.getElementById('noItems')?.remove();
        body.innerHTML = items.length ? items.map((it,idx)=>`
            <tr>
                <td>${it.name}${it.has_imei?' <span class="badge-pill badge-info">IMEI</span>':''}<input type="hidden" name="product_id[]" value="${it.product_id}"></td>
                <td><input type="number" min="1" step="1" name="quantity[]" class="form-control form-control-sm" value="${it.quantity}" oninput="upd(${idx},'quantity',this.value)"></td>
                <td><input type="number" min="0" step="0.01" name="unit_cost[]" class="form-control form-control-sm" value="${it.unit_cost}" oninput="upd(${idx},'unit_cost',this.value)"></td>
                <td>${it.has_imei
                    ? `<textarea name="imei_list[]" class="form-control form-control-sm" rows="2" data-imei-chips data-imei-expected="${it.quantity}" data-imei-placeholder="Scan or type, then Enter" oninput="updImei(${idx},this.value)">${it.imei_list||''}</textarea>`
                    : `<input type="hidden" name="imei_list[]" value=""><span class="text-muted-2">—</span>`}</td>
                <td class="fw-800">${Nubia.fmtMoney(it.quantity*it.unit_cost)}</td>
                <td><button type="button" class="icon-btn" style="width:32px;height:32px;" onclick="removeItem(${idx})"><span class="material-symbols-rounded" style="font-size:17px;color:var(--danger)">close</span></button></td>
            </tr>`).join('') : '<tr id="noItems"><td colspan="6" class="text-center text-muted-2 py-3">No products added yet.</td></tr>';
        totals();
    }
    window.upd = function(idx,field,val){ items[idx][field] = parseFloat(val)||0; render(); };
    window.updImei = function(idx,val){ items[idx].imei_list = val; };

    function totals(){
        const sub = items.reduce((s,i)=>s+i.quantity*i.unit_cost,0);
        const disc = parseFloat(document.getElementById('fDiscount').value)||0;
        const tax = parseFloat(document.getElementById('fTax').value)||0;
        const ship = parseFloat(document.getElementById('fShipping').value)||0;
        const paid = parseFloat(document.getElementById('fPaid').value)||0;
        const total = Math.max(0, sub - disc + tax + ship);
        document.getElementById('sumSubtotal').textContent = Nubia.fmtMoney(sub);
        document.getElementById('sumTotal').textContent = Nubia.fmtMoney(total);
        document.getElementById('sumDue').textContent = Nubia.fmtMoney(Math.max(0,total-paid));
    }
    ['fDiscount','fTax','fShipping','fPaid'].forEach(id=>document.getElementById(id).addEventListener('input',totals));

    document.getElementById('purchaseForm').addEventListener('submit', e=>{
        if(items.length===0){ e.preventDefault(); Nubia.toast('Add at least one product.','error'); return; }
        for (const it of items) {
            if (!it.has_imei) continue;
            const lines = (it.imei_list||'').split(/[\s,;]+/).map(s=>s.trim()).filter(Boolean);
            if (lines.length !== Math.round(it.quantity)) {
                e.preventDefault();
                Nubia.toast(it.name + ': enter exactly ' + Math.round(it.quantity) + ' IMEI(s).', 'error');
                return;
            }
        }
    });
})();
JS; ?>
