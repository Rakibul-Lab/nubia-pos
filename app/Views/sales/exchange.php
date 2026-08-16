<?php
use App\Core\View;
/** @var array $sale @var array $items */
?>
<?= View::partial('components.page_head', [
    'title'    => 'Exchange',
    'subtitle' => 'Invoice ' . e($sale['invoice_no']) . ' · ' . e($sale['customer_name'] ?? 'Walk-in Customer'),
    'extraActions' => '<a href="' . url('sales/' . $sale['id']) . '" class="btn btn-soft">Back to Sale</a>',
]) ?>

<form method="POST" action="<?= url('sales/' . $sale['id'] . '/exchange') ?>" id="exchangeForm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Return items</span>
                    <span class="text-muted-2" style="font-size:.78rem;">Credit at original sale price</span>
                </div>
                <div class="table-wrap">
                    <table class="nubia" id="returnTable">
                        <thead>
                            <tr>
                                <th style="width:44px;"></th>
                                <th>Product</th>
                                <th>Sold</th>
                                <th>Available</th>
                                <th>Price</th>
                                <th style="width:110px;">Return Qty</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <?php $max = (float) $it['returnable_qty']; ?>
                            <tr class="return-row"
                                data-price="<?= e((string) $it['unit_price']) ?>"
                                data-max="<?= e((string) $max) ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input return-check" checked>
                                    <input type="hidden" name="sale_item_id[]" value="<?= (int) $it['id'] ?>" class="return-id">
                                </td>
                                <td>
                                    <div class="fw-800"><?= e($it['product_name']) ?></div>
                                    <div class="text-muted-2" style="font-size:.75rem;"><?= e($it['sku']) ?></div>
                                </td>
                                <td><?= rtrim(rtrim(number_format((float) $it['quantity'], 2), '0'), '.') ?></td>
                                <td><span class="badge-pill badge-success"><?= rtrim(rtrim(number_format($max, 2), '0'), '.') ?></span></td>
                                <td><?= money($it['unit_price']) ?></td>
                                <td>
                                    <input type="number" name="return_qty[]" class="form-control form-control-sm return-qty"
                                           min="0" max="<?= $max ?>" step="1" value="<?= (int) max(1, $max) ?>">
                                </td>
                                <td class="text-end fw-800 return-credit">—</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Replacement products</div>
                <div class="card-body">
                    <div class="position-relative mb-3">
                        <input type="text" id="exProdSearch" class="form-control" placeholder="Scan barcode or search replacement…" autocomplete="off" autofocus>
                        <div class="search-results" id="exProdResults" style="position:absolute;"></div>
                    </div>
                    <div class="table-wrap">
                        <table class="nubia">
                            <thead><tr><th>Product</th><th style="width:100px;">Qty</th><th style="width:130px;">Price</th><th style="width:120px;">Subtotal</th><th></th></tr></thead>
                            <tbody id="exItemsBody">
                                <tr id="exNoItems"><td colspan="5" class="text-center text-muted-2 py-3">Add replacement products</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">Settlement</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-1"><span class="text-muted-2">Return credit</span><span class="fw-800" id="exReturnTotal"><?= money(0) ?></span></div>
                    <div class="d-flex justify-content-between py-1"><span class="text-muted-2">New items total</span><span class="fw-800" id="exNewTotal"><?= money(0) ?></span></div>
                    <hr class="divider">
                    <div class="d-flex justify-content-between py-1"><span class="fw-800" id="exDiffLabel">Difference</span><span class="fw-800" id="exDiff" style="color:var(--brand);"><?= money(0) ?></span></div>
                    <p class="text-muted-2 mb-3 mt-2" id="exDiffHint" style="font-size:.8rem;">Select return qty and add replacements to see settlement.</p>

                    <div class="mb-3" id="exPayBlock">
                        <label class="form-label" for="exPaid">Amount to collect</label>
                        <input type="number" name="paid" id="exPaid" class="form-control" min="0" step="0.01" placeholder="0" value="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="exMethod">Payment / refund method</label>
                        <select name="payment_method" id="exMethod" class="form-select">
                            <?php foreach (payment_methods(!empty($sale['customer_id'])) as $value => $label): ?>
                                <option value="<?= e($value) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="exReason">Reason (optional)</label>
                        <input type="text" name="reason" id="exReason" class="form-control" placeholder="Size change, defective, customer request…">
                    </div>

                    <button type="submit" class="btn btn-brand w-100" id="exSubmit">
                        <span class="material-symbols-rounded">swap_horiz</span> Complete Exchange
                    </button>
                </div>
            </div>

            <div class="glass p-3">
                <div class="fw-800 mb-1" style="font-size:.9rem;">How it works</div>
                <ul class="text-muted-2 mb-0 ps-3" style="font-size:.8rem;line-height:1.55;">
                    <li>Returned items go back to stock at this warehouse.</li>
                    <li>Replacements create a new linked invoice.</li>
                    <li>If new total is higher, collect the difference.</li>
                    <li>If lower, refund the difference to the customer.</li>
                    <li>Equal totals = even swap (no cash movement).</li>
                </ul>
            </div>
        </div>
    </div>
</form>

<?php
$warehouseId = (int) $sale['warehouse_id'];
$pageScript = <<<'JS'
(function(){
    const warehouseId = __WAREHOUSE__;
    let items = [];
    const body = document.getElementById('exItemsBody');
    const search = document.getElementById('exProdSearch');
    const results = document.getElementById('exProdResults');
    let timer = null;

    function money(n){ return Nubia.fmtMoney(n); }

    function returnCredit() {
        let total = 0;
        document.querySelectorAll('.return-row').forEach(row => {
            const check = row.querySelector('.return-check');
            const qtyInput = row.querySelector('.return-qty');
            const idInput = row.querySelector('.return-id');
            const price = parseFloat(row.dataset.price) || 0;
            const max = parseFloat(row.dataset.max) || 0;
            let qty = parseFloat(qtyInput.value) || 0;
            if (!check.checked) {
                qty = 0;
                qtyInput.disabled = true;
                idInput.disabled = true;
            } else {
                qtyInput.disabled = false;
                idInput.disabled = false;
                if (qty > max) { qty = max; qtyInput.value = max; }
                if (qty < 0) { qty = 0; qtyInput.value = 0; }
            }
            const credit = Math.round(qty * price * 100) / 100;
            row.querySelector('.return-credit').textContent = money(credit);
            total += credit;
        });
        return total;
    }

    function newTotal() {
        return items.reduce((s, i) => s + i.quantity * i.unit_price, 0);
    }

    function settle() {
        const ret = returnCredit();
        const neu = newTotal();
        const diff = Math.round((neu - ret) * 100) / 100;
        document.getElementById('exReturnTotal').textContent = money(ret);
        document.getElementById('exNewTotal').textContent = money(neu);
        const label = document.getElementById('exDiffLabel');
        const diffEl = document.getElementById('exDiff');
        const hint = document.getElementById('exDiffHint');
        const payBlock = document.getElementById('exPayBlock');
        const paid = document.getElementById('exPaid');

        if (diff > 0) {
            label.textContent = 'Customer pays';
            diffEl.style.color = 'var(--brand)';
            diffEl.textContent = money(diff);
            hint.textContent = 'New items cost more. Collect the difference from the customer.';
            payBlock.style.display = '';
            if (paid.value === '' || paid.dataset.auto === '1') {
                paid.value = diff.toFixed(2);
                paid.dataset.auto = '1';
            }
        } else if (diff < 0) {
            label.textContent = 'Refund to customer';
            diffEl.style.color = 'var(--success)';
            diffEl.textContent = money(Math.abs(diff));
            hint.textContent = 'Return credit is higher. Refund this amount using the selected method.';
            payBlock.style.display = 'none';
            paid.value = '';
            paid.dataset.auto = '1';
        } else {
            label.textContent = 'Difference';
            diffEl.style.color = 'var(--text)';
            diffEl.textContent = money(0);
            hint.textContent = 'Even exchange — no cash to collect or refund.';
            payBlock.style.display = 'none';
            paid.value = '';
            paid.dataset.auto = '1';
        }
    }

    document.getElementById('exPaid').addEventListener('input', function () { this.dataset.auto = '0'; });
    document.querySelectorAll('.return-check, .return-qty').forEach(el => {
        el.addEventListener('change', settle);
        el.addEventListener('input', settle);
    });

    window.exUpd = function (i, field, val) {
        items[i][field] = parseFloat(val) || 0;
        if (items[i].quantity <= 0) items.splice(i, 1);
        render();
    };
    window.exRemove = function (i) { items.splice(i, 1); render(); };

    function addItem(p) {
        const ex = items.find(i => i.product_id == p.id);
        if (ex) { ex.quantity += 1; }
        else {
            items.push({
                product_id: parseInt(p.id, 10),
                name: p.name,
                quantity: 1,
                unit_price: parseFloat(p.selling_price) || 0
            });
        }
        render();
    }

    function render() {
        if (!items.length) {
            body.innerHTML = '<tr id="exNoItems"><td colspan="5" class="text-center text-muted-2 py-3">Add replacement products</td></tr>';
        } else {
            body.innerHTML = items.map((it, idx) =>
                '<tr>' +
                '<td>' + it.name + '<input type="hidden" name="product_id[]" value="' + it.product_id + '"></td>' +
                '<td><input type="number" min="1" step="1" name="quantity[]" class="form-control form-control-sm" value="' + it.quantity + '" oninput="exUpd(' + idx + ',\'quantity\',this.value)"></td>' +
                '<td><input type="number" min="0" step="0.01" name="unit_price[]" class="form-control form-control-sm" value="' + it.unit_price + '" oninput="exUpd(' + idx + ',\'unit_price\',this.value)"></td>' +
                '<td class="fw-800">' + money(it.quantity * it.unit_price) + '</td>' +
                '<td><button type="button" class="icon-btn" style="width:32px;height:32px;" onclick="exRemove(' + idx + ')"><span class="material-symbols-rounded" style="font-size:17px;color:var(--danger)">close</span></button></td>' +
                '</tr>'
            ).join('');
        }
        settle();
    }

    search.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 1) { results.style.display = 'none'; return; }
        timer = setTimeout(async () => {
            const { data } = await Nubia.ajax('/api/products/search?q=' + encodeURIComponent(q) + '&warehouse=' + warehouseId);
            const list = data.results || [];
            results.innerHTML = list.map(p =>
                '<a href="#"><strong>' + p.name + '</strong> <span class="text-muted-2" style="font-size:.8rem">' +
                (p.sku || '') + ' · ' + money(p.selling_price) + ' · Stock ' + Math.floor(p.stock || 0) + '</span></a>'
            ).join('') || '<div class="p-2 text-muted-2">No products</div>';
            results.style.display = 'block';
            results.querySelectorAll('a').forEach((a, i) => {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    addItem(list[i]);
                    results.style.display = 'none';
                    search.value = '';
                    search.focus();
                });
            });
        }, 200);
    });

    search.addEventListener('keydown', async function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const q = this.value.trim();
        if (!q) return;
        const { data } = await Nubia.ajax('/api/products/search?q=' + encodeURIComponent(q) + '&warehouse=' + warehouseId);
        const list = data.results || [];
        if (list.length) {
            addItem(list[0]);
            results.style.display = 'none';
            this.value = '';
        } else {
            Nubia.toast('Product not found', 'error');
        }
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.position-relative')) results.style.display = 'none';
    });

    document.getElementById('exchangeForm').addEventListener('submit', function (e) {
        const ret = returnCredit();
        if (ret <= 0) {
            e.preventDefault();
            Nubia.toast('Select at least one item to return', 'error');
            return;
        }
        if (!items.length) {
            e.preventDefault();
            Nubia.toast('Add at least one replacement product', 'error');
        }
    });

    settle();
})();
JS;
$pageScript = str_replace('__WAREHOUSE__', (string) $warehouseId, $pageScript);
?>
