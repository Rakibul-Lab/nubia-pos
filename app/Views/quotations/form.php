<?php
use App\Core\View;
/** @var array $customers */
?>
<?= View::partial('components.page_head', ['title' => 'New Quotation', 'subtitle' => 'Create a customer estimate']) ?>
<form method="POST" action="<?= url('quotations') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-header">Quotation Info</div><div class="card-body"><div class="row g-3">
                <div class="col-md-5"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><option value="">Walk-in</option><?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Valid Until</label><input type="date" name="valid_until" class="form-control"></div>
            </div></div></div>
            <div class="card"><div class="card-header">Products</div><div class="card-body">
                <div class="position-relative mb-3"><input type="text" id="prodSearch" class="form-control" placeholder="Search product…" autocomplete="off"><div class="search-results" id="prodResults"></div></div>
                <div class="table-wrap"><table class="nubia">
                    <thead><tr><th>Product</th><th style="width:100px;">Qty</th><th style="width:130px;">Price</th><th style="width:120px;">Subtotal</th><th></th></tr></thead>
                    <tbody id="itemsBody"><tr id="noItems"><td colspan="5" class="text-center text-muted-2 py-3">No products added yet.</td></tr></tbody>
                </table></div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card"><div class="card-header">Summary</div><div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Subtotal</span><span class="fw-800" id="sumSubtotal"><?= money(0) ?></span></div>
                <div class="mb-2"><label class="form-label">Discount</label><input type="number" min="0" step="0.01" name="discount" id="fDiscount" class="form-control" value="0"></div>
                <div class="mb-2"><label class="form-label">Tax</label><input type="number" min="0" step="0.01" name="tax" id="fTax" class="form-control" value="0"></div>
                <div class="d-flex justify-content-between py-2 mt-2" style="border-top:2px solid var(--brand);"><span class="fw-800">Total</span><span class="fw-800" id="sumTotal" style="font-size:1.15rem;"><?= money(0) ?></span></div>
                <div class="mb-2 mt-2"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
                <div class="d-grid mt-2"><button class="btn btn-brand py-2"><span class="material-symbols-rounded">save</span> Create Quotation</button></div>
            </div></div>
        </div>
    </div>
</form>
<?php $pageScript = <<<'JS'
(function(){
    let items=[]; const body=document.getElementById('itemsBody'),search=document.getElementById('prodSearch'),results=document.getElementById('prodResults'); let timer;
    search.addEventListener('input',function(){clearTimeout(timer);const q=this.value.trim();if(q.length<1){results.style.display='none';return;}
        timer=setTimeout(async()=>{const {data}=await Nubia.ajax('/api/products/search?q='+encodeURIComponent(q));const list=data.results||[];
            results.innerHTML=list.map(p=>`<a href="#" data-p='${JSON.stringify(p)}'><strong>${p.name}</strong> <span class="text-muted-2" style="font-size:.8rem">${p.sku} · ${Nubia.fmtMoney(p.selling_price)}</span></a>`).join('')||'<div class="p-2 text-muted-2">No products</div>';
            results.style.display='block';
            results.querySelectorAll('a').forEach(a=>a.addEventListener('click',e=>{e.preventDefault();addItem(JSON.parse(a.dataset.p));results.style.display='none';search.value='';search.focus();}));},200);});
    document.addEventListener('click',e=>{if(!e.target.closest('.position-relative'))results.style.display='none';});
    function addItem(p){const ex=items.find(i=>i.product_id==p.id);if(ex){ex.quantity++;}else{items.push({product_id:p.id,name:p.name,quantity:1,unit_price:parseFloat(p.selling_price)});}render();}
    window.removeItem=(i)=>{items.splice(i,1);render();};
    window.upd=(i,f,v)=>{items[i][f]=parseFloat(v)||0;render();};
    function render(){body.innerHTML=items.length?items.map((it,idx)=>`<tr><td>${it.name}<input type="hidden" name="product_id[]" value="${it.product_id}"></td><td><input type="number" min="1" step="0.01" name="quantity[]" class="form-control form-control-sm" value="${it.quantity}" oninput="upd(${idx},'quantity',this.value)"></td><td><input type="number" min="0" step="0.01" name="unit_price[]" class="form-control form-control-sm" value="${it.unit_price}" oninput="upd(${idx},'unit_price',this.value)"></td><td class="fw-800">${Nubia.fmtMoney(it.quantity*it.unit_price)}</td><td><button type="button" class="icon-btn" style="width:32px;height:32px;" onclick="removeItem(${idx})"><span class="material-symbols-rounded" style="font-size:17px;color:var(--danger)">close</span></button></td></tr>`).join(''):'<tr id="noItems"><td colspan="5" class="text-center text-muted-2 py-3">No products added yet.</td></tr>';totals();}
    function totals(){const sub=items.reduce((s,i)=>s+i.quantity*i.unit_price,0);const disc=parseFloat(document.getElementById('fDiscount').value)||0;const tax=parseFloat(document.getElementById('fTax').value)||0;document.getElementById('sumSubtotal').textContent=Nubia.fmtMoney(sub);document.getElementById('sumTotal').textContent=Nubia.fmtMoney(Math.max(0,sub-disc+tax));}
    document.getElementById('fDiscount').addEventListener('input',totals);document.getElementById('fTax').addEventListener('input',totals);
})();
JS; ?>
