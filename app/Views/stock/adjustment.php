<?php
use App\Core\View;
/** @var array $warehouses */
?>
<?= View::partial('components.page_head', ['title' => 'Stock Adjustment', 'subtitle' => 'Add or remove stock manually']) ?>
<form method="POST" action="<?= url('stock/adjustment') ?>">
    <?= csrf_field() ?>
    <div class="row g-3 justify-content-center"><div class="col-lg-7">
        <div class="card"><div class="card-body">
            <div class="mb-3 position-relative">
                <label class="form-label">Product *</label>
                <input type="text" id="prodSearch" class="form-control" placeholder="Search product by name or SKU..." autocomplete="off">
                <input type="hidden" name="product_id" id="productId" required>
                <div id="prodResults" class="search-results" style="display:none;"></div>
                <div id="selected" class="text-muted-2 mt-1" style="font-size:.85rem;"></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Warehouse</label><select name="warehouse_id" class="form-select"><?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>"><?= e($w['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Type</label><select name="type" class="form-select"><option value="addition">Addition (+)</option><option value="subtraction">Subtraction (−)</option></select></div>
                <div class="col-md-6"><label class="form-label">Quantity *</label><input type="number" name="quantity" id="adjQty" class="form-control" min="0.01" step="0.01" required></div>
                <div class="col-md-6"><label class="form-label">Reason</label><input type="text" name="reason" class="form-control" placeholder="e.g. Damaged, Recount"></div>
                <div class="col-12" id="imeiAdjWrap" style="display:none;">
                    <label class="form-label">IMEI / Serials (required for IMEI products)</label>
                    <textarea name="imei_list" id="imeiAdjList" class="form-control" rows="4" data-imei-chips data-imei-placeholder="Scan or type an IMEI, then press Enter — must equal quantity"></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2"><button class="btn btn-brand"><span class="material-symbols-rounded">save</span> Apply Adjustment</button><a href="<?= url('stock') ?>" class="btn btn-soft">Cancel</a></div>
        </div></div>
    </div></div>
</form>
<?php $pageScript = <<<'JS'
(function(){
    const search=document.getElementById('prodSearch'),results=document.getElementById('prodResults'),pid=document.getElementById('productId'),sel=document.getElementById('selected');
    const imeiWrap=document.getElementById('imeiAdjWrap');
    let tracks=false;
    let timer;
    search.addEventListener('input',function(){
        clearTimeout(timer); const q=this.value.trim();
        if(q.length<1){results.style.display='none';return;}
        timer=setTimeout(async()=>{
            const {data}=await Nubia.ajax('/api/products/search?q='+encodeURIComponent(q));
            const list=data.results||[];
            results.innerHTML=list.map(p=>`<a href="#" data-p='${JSON.stringify(p).replace(/'/g,"&#39;")}'><strong>${p.name}</strong> <span class="text-muted-2" style="font-size:.8rem">${p.sku} · Stock ${p.stock}${Number(p.has_imei)||Number(p.has_serial)?' · IMEI':''}</span></a>`).join('')||'<div class="p-2 text-muted-2">No products</div>';
            results.style.display='block';
            results.querySelectorAll('a').forEach(a=>a.addEventListener('click',e=>{
                e.preventDefault();
                const p=JSON.parse(a.getAttribute('data-p'));
                pid.value=p.id;
                tracks = Number(p.has_imei)===1 || Number(p.has_serial)===1;
                imeiWrap.style.display = tracks ? '' : 'none';
                sel.textContent='Selected: '+p.name+' ('+p.sku+')'+(tracks?' · IMEI tracked':'');
                search.value=p.name;
                results.style.display='none';
            }));
        },200);
    });
    document.addEventListener('click',e=>{if(!e.target.closest('.position-relative'))results.style.display='none';});

    const qty=document.getElementById('adjQty'), imeiList=document.getElementById('imeiAdjList');
    qty.addEventListener('input',()=>imeiList.dataset.imeiExpected=qty.value||'');
})();
JS; ?>
