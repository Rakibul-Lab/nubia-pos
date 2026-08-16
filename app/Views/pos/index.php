<?php
/** @var array $products @var array $categories @var array $customers @var array $warehouses @var int $defaultWarehouse */
?>
<div class="pos-layout" id="posLayout">
    <!-- Products panel -->
    <div class="d-flex flex-column" style="min-height:0;">
        <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
            <div class="global-search pos-search-wrap flex-grow-1" style="max-width:none;">
                <span class="material-symbols-rounded">barcode_scanner</span>
                <input type="text" id="posSearch" placeholder="Scan barcode / IMEI / SKU or search…" autocomplete="off" autofocus role="combobox" aria-autocomplete="list" aria-controls="posSearchResults" aria-expanded="false" inputmode="search" enterkeyhint="search">
                <button type="button" id="posSearchClear" class="pos-search-clear" aria-label="Clear search" hidden>
                    <span class="material-symbols-rounded">close</span>
                </button>
                <div id="posSearchResults" class="pos-search-results search-results" role="listbox" aria-label="Product matches"></div>
            </div>
            <button type="button" class="btn btn-soft" id="posCameraScanBtn" title="Scan with camera">
                <span class="material-symbols-rounded">photo_camera</span>
                <span class="d-none d-sm-inline">Camera</span>
            </button>
            <select id="posWarehouse" class="form-select" style="width:auto;">
                <?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>" <?= (int) $w['id'] === $defaultWarehouse ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?>
            </select>
            <?php if (can('direct.create')): ?><a href="<?= url('direct-buy-sell/create') ?>" class="btn btn-brand"><span class="material-symbols-rounded">bolt</span> Direct Buy &amp; Sell</a><?php endif; ?>
        </div>
        <div class="pos-products glass p-3">
            <div class="pos-grid" id="posGrid">
                <?php foreach ($products as $p): ?>
                    <div class="pos-tile"
                         data-id="<?= (int) $p['id'] ?>"
                         data-name="<?= e($p['name']) ?>"
                         data-price="<?= e((string) $p['selling_price']) ?>"
                         data-stock="<?= e((string) $p['stock']) ?>"
                         data-sku="<?= e($p['sku'] ?? '') ?>"
                         data-barcode="<?= e($p['barcode'] ?? '') ?>"
                         data-has-imei="<?= ((int) ($p['has_imei'] ?? 0) === 1 || (int) ($p['has_serial'] ?? 0) === 1) ? '1' : '0' ?>">
                        <div class="thumb"><span class="material-symbols-rounded">inventory_2</span></div>
                        <div class="fw-800" style="font-size:.82rem;line-height:1.2;"><?= e(mb_strimwidth($p['name'], 0, 34, '…')) ?></div>
                        <div class="text-muted-2" style="font-size:.72rem;"><?= money($p['selling_price']) ?> <span class="pos-inc-vat">inc.vat</span></div>
                        <div class="badge-pill <?= (float) $p['stock'] > 0 ? 'badge-success' : 'badge-danger' ?> mt-1" style="font-size:.62rem;"><?= (float) $p['stock'] > 0 ? ((int) $p['stock'] . ' in stock') : 'Out' ?><?= ((int) ($p['has_imei'] ?? 0) || (int) ($p['has_serial'] ?? 0)) ? ' · IMEI' : '' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="posEmpty" class="empty-state" style="display:none;"><span class="material-symbols-rounded">search_off</span><p>No products match your search.</p></div>
        </div>
    </div>

    <!-- Cart panel -->
    <div class="card pos-cart" id="posCartPanel">
        <div class="pos-cart-head">
            <div class="pos-cart-head-left">
                <span class="pos-cart-icon"><span class="material-symbols-rounded">shopping_cart</span></span>
                <div>
                    <div class="pos-cart-title-main">Cart</div>
                    <div class="pos-cart-count" id="cartCountLabel">0 items</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="pos-text-size" title="Adjust text size">
                    <button type="button" class="btn btn-soft btn-sm pos-fs-btn" id="posFontDown" aria-label="Smaller text">A−</button>
                    <button type="button" class="btn btn-soft btn-sm pos-fs-btn" id="posFontUp" aria-label="Larger text">A+</button>
                </div>
                <button type="button" class="btn btn-danger-soft btn-sm" onclick="clearCart()">Clear</button>
            </div>
        </div>
        <div class="pos-cart-customer">
            <label class="pos-field-label">Customer</label>
            <select id="posCustomer" class="form-select">
                <option value="">Walk-in Customer</option>
                <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="pos-cart-items" id="cartItems">
            <div class="empty-state" id="cartEmpty"><span class="material-symbols-rounded">remove_shopping_cart</span><p class="mb-0">Cart is empty</p></div>
        </div>
        <div class="pos-cart-footer">
            <div class="pos-discount-wrap">
                <label class="pos-field-label" for="posDiscount">Discount</label>
                <div class="pos-discount-row">
                    <input type="number" id="posDiscount" class="form-control" placeholder="0" value="" min="0" step="1">
                    <select id="posDiscountType" class="form-select" aria-label="Discount type">
                        <option value="fixed">Amount</option>
                        <option value="percent">%</option>
                    </select>
                </div>
            </div>
            <div class="pos-summary">
                <div class="pos-summary-row">
                    <span>Subtotal</span>
                    <span id="cartSubtotal"><?= money(0) ?> <span class="pos-inc-vat">inc.vat</span></span>
                </div>
                <div class="pos-summary-row" id="cartDiscountRow" style="display:none;">
                    <span>Discount</span>
                    <span id="cartDiscountVal">−<?= money(0) ?></span>
                </div>
                <div class="pos-summary-total">
                    <span>Total <small class="pos-inc-vat">inc.vat</small></span>
                    <span id="cartTotal"><?= money(0) ?></span>
                </div>
                <div class="pos-summary-row pos-due-row" id="cartDueRow">
                    <span>Due</span>
                    <span id="cartDue" class="pos-due-amount"><?= money(0) ?> <span class="pos-inc-vat">inc.vat</span></span>
                </div>
                <div class="pos-summary-row pos-change-row" id="cartChangeRow" style="display:none;">
                    <span>Change</span>
                    <span id="cartChange"><?= money(0) ?> <span class="pos-inc-vat">inc.vat</span></span>
                </div>
            </div>
            <div class="pos-pay-row">
                <div>
                    <label class="pos-field-label" for="posPaid">Paid amount</label>
                    <input type="number" id="posPaid" class="form-control" placeholder="0" min="0" step="1">
                </div>
                <div>
                    <label class="pos-field-label" for="posMethod">Payment</label>
                    <select id="posMethod" class="form-select">
                        <?php foreach (payment_methods(true) as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if (can('pos.checkout')): ?>
                <button class="btn btn-brand w-100 pos-checkout-btn" id="posCheckout">
                    <span class="material-symbols-rounded">check_circle</span> Complete Sale
                </button>
            <?php else: ?>
                <button class="btn btn-soft w-100 pos-checkout-btn" type="button" disabled title="Checkout permission required">
                    <span class="material-symbols-rounded">lock</span> Checkout not permitted
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quantity modal -->
<div class="modal fade" id="posScanModal" tabindex="-1" aria-labelledby="posScanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pos-scan-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="posScanModalLabel">Scan barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted-2 mb-3" style="font-size:.88rem;">Point your phone camera at the product barcode. Hardware USB/Bluetooth scanners also work in the search box.</p>
                <div id="posScanReader" class="pos-scan-reader"></div>
                <div id="posScanStatus" class="text-muted-2 mt-2 text-center" style="font-size:.82rem;">Starting camera…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="posQtyModal" tabindex="-1" aria-labelledby="posQtyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered pos-qty-dialog">
        <div class="modal-content pos-qty-modal">
            <div class="pos-qty-modal-glow" aria-hidden="true"></div>
            <button type="button" class="pos-qty-close" data-bs-dismiss="modal" aria-label="Close">
                <span class="material-symbols-rounded">close</span>
            </button>

            <div class="pos-qty-hero">
                <div class="pos-qty-thumb" aria-hidden="true">
                    <span class="material-symbols-rounded">inventory_2</span>
                </div>
                <div class="pos-qty-hero-copy">
                    <div class="pos-qty-eyebrow" id="posQtyModalLabel">Add to cart</div>
                    <h5 class="pos-qty-name" id="posModalName">—</h5>
                    <div class="pos-qty-meta">
                        <span class="pos-qty-price" id="posModalPrice">—</span>
                        <span class="pos-qty-stock-pill" id="posModalStockPill">
                            <span class="material-symbols-rounded">warehouse</span>
                            <span id="posModalStock">—</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="pos-qty-body">
                <div id="posQtyNormalBlock">
                    <div class="pos-qty-label-row">
                        <label class="pos-qty-label" for="posModalQty">Quantity</label>
                        <span class="pos-qty-keys"><kbd>↑</kbd><kbd>↓</kbd> adjust · <kbd>Enter</kbd> add</span>
                    </div>
                    <div class="pos-qty-stepper pos-qty-stepper-lg">
                        <button type="button" class="pos-qty-btn pos-qty-btn-lg" id="posQtyMinus" tabindex="-1" aria-label="Decrease quantity">
                            <span class="material-symbols-rounded">remove</span>
                        </button>
                        <input type="number" id="posModalQty" class="pos-qty-input pos-qty-input-lg" value="1" min="1" step="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <button type="button" class="pos-qty-btn pos-qty-btn-lg" id="posQtyPlus" tabindex="-1" aria-label="Increase quantity">
                            <span class="material-symbols-rounded">add</span>
                        </button>
                    </div>
                </div>

                <div id="posImeiBlock" class="d-none">
                    <div class="pos-qty-label-row">
                        <label class="pos-qty-label" for="posModalImei">IMEI / Serial</label>
                        <span class="pos-qty-keys" id="posImeiCount">0 selected</span>
                    </div>
                    <div class="pos-imei-field">
                        <div class="pos-imei-chips" id="posImeiChips"></div>
                        <input type="text" id="posModalImei" class="form-control pos-imei-input" placeholder="Scan or search IMEI…" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="posImeiSuggest" aria-expanded="false">
                        <div class="form-text mt-1" id="posImeiHint">Pick from the list below or scan to add.</div>
                        <div class="pos-imei-suggest" id="posImeiSuggest" role="listbox" aria-label="Available IMEIs"></div>
                    </div>
                </div>

                <div class="pos-qty-line">
                    <span>Line total</span>
                    <strong id="posModalLineTotal">—</strong>
                </div>
            </div>

            <div class="pos-qty-actions">
                <button type="button" class="btn btn-soft pos-qty-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-brand pos-qty-add" id="posModalAdd">
                    <span class="material-symbols-rounded">add_shopping_cart</span>
                    <span>Add to Cart</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php $pageScript = <<<'JS'
(function(){
    let cart = [];
    let pendingProduct = null;
    let selectedImeis = [];
    const grid = document.getElementById('posGrid');
    const cartBox = document.getElementById('cartItems');
    const search = document.getElementById('posSearch');
    const searchClear = document.getElementById('posSearchClear');
    const searchWrap = search.closest('.pos-search-wrap');
    const qtyModalEl = document.getElementById('posQtyModal');
    const qtyModal = new bootstrap.Modal(qtyModalEl);
    const qtyInput = document.getElementById('posModalQty');
    const imeiInput = document.getElementById('posModalImei');
    const posLayout = document.getElementById('posLayout');

    const toQty = (v) => Math.max(1, parseInt(String(v), 10) || 1);
    const fmtStock = (n) => String(Math.floor(parseFloat(n) || 0));
    const fmtMoney = (n) => Nubia.fmtMoney(n) + ' <span class="pos-inc-vat">inc.vat</span>';
    const fmtMoneyText = (n) => Nubia.fmtMoney(n) + ' inc.vat';
    const parseImeis = (raw) => String(raw || '').split(/[\s,;]+/).map(s => s.trim().toUpperCase()).filter(Boolean)
        .filter((v, i, a) => a.indexOf(v) === i);

    function productFromTile(tile) {
        return {
            id: parseInt(tile.dataset.id, 10),
            name: tile.dataset.name,
            price: parseFloat(tile.dataset.price) || 0,
            stock: Math.floor(parseFloat(tile.dataset.stock) || 0),
            sku: tile.dataset.sku || '',
            has_imei: tile.dataset.hasImei === '1',
            matched_imei: ''
        };
    }

    function productFromApi(p) {
        return {
            id: parseInt(p.id, 10),
            name: p.name,
            price: parseFloat(p.selling_price) || 0,
            stock: Math.floor(parseFloat(p.stock) || 0),
            sku: p.sku || '',
            has_imei: Number(p.has_imei) === 1 || Number(p.has_serial) === 1,
            matched_imei: p.matched_imei || ''
        };
    }

    function setModalQty(v) {
        qtyInput.value = toQty(v);
        updateModalLineTotal();
    }

    function updateModalLineTotal() {
        const lineEl = document.getElementById('posModalLineTotal');
        if (!lineEl || !pendingProduct) return;
        let qty = toQty(qtyInput.value);
        if (pendingProduct.has_imei) {
            qty = Math.max(1, selectedImeis.length || 1);
        }
        lineEl.innerHTML = fmtMoney(pendingProduct.price * qty);
    }

    function openQtyModal(product, qty = 1) {
        pendingProduct = product;
        document.getElementById('posModalName').textContent = product.name;
        document.getElementById('posModalPrice').innerHTML = fmtMoney(product.price);
        const stockLabel = product.stock > 0 ? (fmtStock(product.stock) + ' in stock') : 'Out of stock';
        document.getElementById('posModalStock').textContent = stockLabel;
        const stockPill = document.getElementById('posModalStockPill');
        if (stockPill) {
            stockPill.classList.toggle('is-out', product.stock <= 0);
            stockPill.classList.toggle('is-ok', product.stock > 0);
        }
        const imeiMode = !!product.has_imei;
        document.getElementById('posQtyNormalBlock').classList.toggle('d-none', imeiMode);
        document.getElementById('posImeiBlock').classList.toggle('d-none', !imeiMode);
        document.getElementById('posQtyModalLabel').textContent = imeiMode ? 'Select IMEI' : 'Add to cart';
        imeiInput.value = '';
        selectedImeis = product.matched_imei ? [String(product.matched_imei).toUpperCase()] : [];
        renderImeiChips();
        setModalQty(qty);
        updateModalLineTotal();
        hideImeiSuggest();
        if (imeiMode) {
            refreshImeiSuggest();
        }
        qtyModal.show();
        qtyModalEl.addEventListener('shown.bs.modal', function focusQty() {
            if (imeiMode) {
                imeiInput.focus();
                imeiInput.select();
            } else {
                qtyInput.focus();
                qtyInput.select();
            }
            qtyModalEl.removeEventListener('shown.bs.modal', focusQty);
        });
    }

    function confirmAddToCart() {
        if (!pendingProduct) return;
        if (pendingProduct.has_imei) {
            const imeis = selectedImeis.slice();
            if (!imeis.length) {
                Nubia.toast('Select at least one IMEI', 'error');
                imeiInput.focus();
                return;
            }
            if (pendingProduct.stock > 0 && imeis.length > pendingProduct.stock) {
                Nubia.toast('Only ' + pendingProduct.stock + ' in stock', 'warning');
                return;
            }
            addToCart(pendingProduct, imeis.length, imeis);
            pendingProduct = null;
            qtyModal.hide();
            clearSearch();
            return;
        }
        const qty = toQty(qtyInput.value);
        if (pendingProduct.stock > 0 && qty > pendingProduct.stock) {
            Nubia.toast('Only ' + pendingProduct.stock + ' in stock', 'warning');
            setModalQty(pendingProduct.stock);
            qtyInput.focus();
            qtyInput.select();
            return;
        }
        addToCart(pendingProduct, qty);
        pendingProduct = null;
        qtyModal.hide();
        clearSearch();
    }

    function addToCart(p, qty, imeis = []) {
        qty = toQty(qty);
        imeis = parseImeis(imeis);
        if (p.has_imei) {
            if (!imeis.length && p.matched_imei) imeis = [String(p.matched_imei).toUpperCase()];
            if (!imeis.length) {
                openQtyModal(p, 1);
                return;
            }
            // One cart line per product; merge IMEIs (reject duplicates already in cart)
            let ex = cart.find(i => i.id == p.id);
            if (!ex) {
                ex = { id: p.id, name: p.name, price: p.price, qty: 0, has_imei: true, imeis: [] };
                cart.push(ex);
            }
            for (const code of imeis) {
                if (ex.imeis.includes(code) || cart.some(c => c.imeis && c.imeis.includes(code))) {
                    Nubia.toast('IMEI already in cart: ' + code, 'warning');
                    continue;
                }
                ex.imeis.push(code);
            }
            ex.qty = ex.imeis.length;
            if (ex.qty <= 0) cart = cart.filter(i => i.id != p.id);
            renderCart();
            Nubia.toast(p.name + ' · ' + imeis.join(', ') + ' added', 'success');
            return;
        }
        const ex = cart.find(i => i.id == p.id && !i.has_imei);
        if (ex) {
            ex.qty = toQty(ex.qty + qty);
        } else {
            cart.push({ id: p.id, name: p.name, price: p.price, qty: qty, has_imei: false, imeis: [] });
        }
        renderCart();
        Nubia.toast(p.name + ' × ' + qty + ' added', 'success');
    }

    window.changeQty = (id, d) => {
        const it = cart.find(i => i.id == id);
        if (!it) return;
        if (it.has_imei) {
            Nubia.toast('Change qty by adding/removing IMEIs', 'info');
            return;
        }
        it.qty = toQty(it.qty + d);
        if (it.qty <= 0) cart = cart.filter(i => i.id != id);
        renderCart();
    };
    window.setQty = (id, v) => {
        const it = cart.find(i => i.id == id);
        if (!it || it.has_imei) return;
        it.qty = toQty(v);
        if (it.qty <= 0) cart = cart.filter(i => i.id != id);
        renderCart();
    };
    window.removeFromCart = (id) => { cart = cart.filter(i => i.id != id); renderCart(); };
    window.clearCart = () => { cart = []; renderCart(); };

    function renderCart() {
        const count = cart.reduce((s, i) => s + i.qty, 0);
        document.getElementById('cartCountLabel').textContent = count + (count === 1 ? ' item' : ' items');
        document.getElementById('cartEmpty').style.display = cart.length ? 'none' : '';
        cartBox.querySelectorAll('.cart-row').forEach(e => e.remove());
        cart.forEach(it => {
            const div = document.createElement('div');
            div.className = 'cart-row pos-cart-row';
            const imeiHtml = it.has_imei && it.imeis && it.imeis.length
                ? `<div class="text-muted-2" style="font-size:.72rem;font-family:ui-monospace,monospace;">${it.imeis.join(', ')}</div>`
                : '';
            const qtyControls = it.has_imei
                ? `<div class="fw-800 px-2">${it.qty}</div>`
                : `<div class="d-flex align-items-center gap-1 pos-qty-stepper">
                    <button type="button" class="icon-btn pos-qty-btn" onclick="changeQty(${it.id},-1)" aria-label="Decrease"><span class="material-symbols-rounded">remove</span></button>
                    <input type="number" class="pos-qty-input" value="${it.qty}" min="1" step="1" inputmode="numeric"
                        onchange="setQty(${it.id},this.value)" onkeydown="if(event.key==='Enter'){setQty(${it.id},this.value);event.preventDefault();}">
                    <button type="button" class="icon-btn pos-qty-btn" onclick="changeQty(${it.id},1)" aria-label="Increase"><span class="material-symbols-rounded">add</span></button>
                </div>`;
            div.innerHTML = `
                <div class="flex-grow-1 pos-cart-name">
                    <div class="fw-800 pos-cart-title">${it.name}${it.has_imei ? ' <span class="badge-pill badge-info">IMEI</span>' : ''}</div>
                    <div class="text-muted-2 pos-cart-price">${fmtMoney(it.price)} each</div>
                    ${imeiHtml}
                </div>
                ${qtyControls}
                <div class="fw-800 pos-cart-line-total">${fmtMoney(it.price * it.qty)}</div>
                <button type="button" class="icon-btn pos-cart-remove" onclick="removeFromCart(${it.id})" aria-label="Remove"><span class="material-symbols-rounded">close</span></button>`;
            cartBox.appendChild(div);
        });
        totals();
    }

    function cartSubtotal() {
        return cart.reduce((s, i) => s + i.price * i.qty, 0);
    }

    function clampDiscountInput(showToast = false) {
        const input = document.getElementById('posDiscount');
        const type = document.getElementById('posDiscountType').value;
        const sub = cartSubtotal();
        let val = parseFloat(input.value);

        if (input.value.trim() === '' || isNaN(val)) return;
        if (val < 0) {
            input.value = 0;
            return;
        }

        if (type === 'percent') {
            if (val > 100) {
                input.value = 100;
                if (showToast) Nubia.toast('Discount cannot exceed 100%', 'warning');
            }
        } else {
            const max = Math.max(0, Math.round(sub * 100) / 100);
            if (val > max) {
                input.value = max;
                if (showToast) Nubia.toast('Discount cannot exceed cart total', 'warning');
            }
        }
    }

    function totals() {
        clampDiscountInput(false);
        const sub = cartSubtotal();
        const discInput = parseFloat(document.getElementById('posDiscount').value) || 0;
        const discType = document.getElementById('posDiscountType').value;
        const disc = discType === 'percent'
            ? Math.min(sub, Math.round(sub * Math.min(discInput, 100) / 100 * 100) / 100)
            : Math.min(sub, discInput);
        const total = Math.max(0, sub - disc);
        const paidInput = document.getElementById('posPaid');
        const paidRaw = paidInput.value.trim();
        const paid = paidRaw === '' ? 0 : (parseFloat(paidRaw) || 0);
        const due = Math.max(0, total - paid);
        const change = Math.max(0, paid - total);

        document.getElementById('cartSubtotal').innerHTML = fmtMoney(sub);
        document.getElementById('cartTotal').innerHTML = Nubia.fmtMoney(total) + ' <span class="pos-inc-vat">inc.vat</span>';

        const discRow = document.getElementById('cartDiscountRow');
        if (disc > 0) {
            discRow.style.display = '';
            const label = discType === 'percent' ? `Discount (${discInput}%)` : 'Discount';
            discRow.querySelector('span').textContent = label;
            document.getElementById('cartDiscountVal').innerHTML = '−' + fmtMoney(disc);
        } else {
            discRow.style.display = 'none';
        }

        document.getElementById('cartDue').innerHTML = fmtMoney(due);
        document.getElementById('cartChange').innerHTML = fmtMoney(change);
        document.getElementById('cartDueRow').style.display = change > 0 ? 'none' : '';
        document.getElementById('cartChangeRow').style.display = change > 0 ? '' : 'none';

        return total;
    }

    document.getElementById('posDiscount').addEventListener('input', () => {
        clampDiscountInput(true);
        totals();
    });
    document.getElementById('posDiscount').addEventListener('blur', () => {
        clampDiscountInput(true);
        totals();
    });
    document.getElementById('posDiscountType').addEventListener('change', () => {
        clampDiscountInput(true);
        totals();
    });
    document.getElementById('posPaid').addEventListener('input', totals);
    document.getElementById('posCustomer').addEventListener('change', totals);
    document.getElementById('posMethod').addEventListener('change', totals);

    // Click product → quantity modal
    grid.addEventListener('click', e => {
        const tile = e.target.closest('.pos-tile');
        if (tile) openQtyModal(productFromTile(tile));
    });

    // -------- Warehouse switching --------
    const warehouseSel = document.getElementById('posWarehouse');
    let lastWarehouse = warehouseSel.value;

    function tileHtml(p) {
        const stock = Math.floor(parseFloat(p.stock) || 0);
        const hasImei = Number(p.has_imei) === 1 || Number(p.has_serial) === 1;
        const name = String(p.name || '');
        const short = name.length > 34 ? (name.slice(0, 33) + '…') : name;
        return `<div class="pos-tile"
             data-id="${parseInt(p.id, 10)}"
             data-name="${escHtml(name)}"
             data-price="${escHtml(p.selling_price)}"
             data-stock="${stock}"
             data-sku="${escHtml(p.sku || '')}"
             data-barcode="${escHtml(p.barcode || '')}"
             data-has-imei="${hasImei ? '1' : '0'}">
            <div class="thumb"><span class="material-symbols-rounded">inventory_2</span></div>
            <div class="fw-800" style="font-size:.82rem;line-height:1.2;">${escHtml(short)}</div>
            <div class="text-muted-2" style="font-size:.72rem;">${Nubia.fmtMoney(p.selling_price)} <span class="pos-inc-vat">inc.vat</span></div>
            <div class="badge-pill badge-success mt-1" style="font-size:.62rem;">${stock} in stock${hasImei ? ' · IMEI' : ''}</div>
        </div>`;
    }

    async function loadGrid() {
        grid.classList.add('is-loading');
        try {
            const { ok, data } = await Nubia.ajax(
                '/api/products/search?q=&limit=60&in_stock=1&warehouse='
                + encodeURIComponent(warehouseSel.value)
            );
            if (!ok) {
                Nubia.toast('Could not load products for this warehouse', 'error');
                return;
            }
            grid.innerHTML = (data.results || []).map(tileHtml).join('');
            filterGrid(search.value.trim());
        } catch (err) {
            Nubia.toast('Could not load products for this warehouse', 'error');
        } finally {
            grid.classList.remove('is-loading');
        }
    }

    warehouseSel.addEventListener('change', async function() {
        if (cart.length) {
            const res = await Swal.fire({
                icon: 'warning',
                title: 'Switch warehouse?',
                text: 'The cart will be cleared — stock and IMEIs belong to the selected warehouse.',
                showCancelButton: true,
                confirmButtonText: 'Switch & clear',
                cancelButtonText: 'Keep cart',
                confirmButtonColor: '#dc2626'
            });
            if (!res.isConfirmed) {
                this.value = lastWarehouse;
                return;
            }
            clearCart();
        }
        lastWarehouse = this.value;
        hideSearchResults();
        await loadGrid();
        search.focus();
    });

    // Modal controls
    document.getElementById('posModalAdd').addEventListener('click', confirmAddToCart);
    document.getElementById('posQtyMinus').addEventListener('click', () => {
        setModalQty(toQty(qtyInput.value) - 1);
        qtyInput.focus();
    });
    document.getElementById('posQtyPlus').addEventListener('click', () => {
        setModalQty(toQty(qtyInput.value) + 1);
        qtyInput.focus();
    });
    qtyInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmAddToCart();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setModalQty(toQty(qtyInput.value) + 1);
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            setModalQty(toQty(qtyInput.value) - 1);
        }
    });
    qtyInput.addEventListener('input', () => {
        qtyInput.value = qtyInput.value.replace(/[^\d]/g, '');
        updateModalLineTotal();
    });
    qtyInput.addEventListener('blur', () => setModalQty(qtyInput.value));

    // -------- IMEI live search inside the quantity modal --------
    const imeiBox = document.getElementById('posImeiSuggest');
    const imeiHint = document.getElementById('posImeiHint');
    const imeiChips = document.getElementById('posImeiChips');
    const imeiCount = document.getElementById('posImeiCount');
    let imeiTimer = null;
    let imeiAbort = null;
    let imeiItems = [];
    let imeiActive = -1;

    /** IMEIs already reserved by the cart for this product. */
    function cartImeis(productId) {
        const line = cart.find(i => i.id == productId);
        return line ? parseImeis(line.imeis || []) : [];
    }

    /** Selected units, shown as removable chips above the scan box. */
    function renderImeiChips() {
        imeiChips.innerHTML = selectedImeis.map(code => `
            <span class="pos-imei-chip">
                <span class="material-symbols-rounded">smartphone</span>
                <span class="pos-imei-chip-code">${escHtml(code)}</span>
                <button type="button" class="pos-imei-chip-x" data-code="${escHtml(code)}"
                        aria-label="Remove ${escHtml(code)}">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </span>`).join('');
        imeiChips.style.display = selectedImeis.length ? 'flex' : 'none';
        if (imeiCount) {
            imeiCount.textContent = selectedImeis.length + ' selected';
        }
        updateModalLineTotal();
    }

    function removeImei(code) {
        selectedImeis = selectedImeis.filter(c => c !== code);
        renderImeiChips();
        refreshImeiSuggest();
    }

    imeiChips.addEventListener('click', e => {
        const btn = e.target.closest('.pos-imei-chip-x');
        if (!btn) return;
        removeImei(btn.dataset.code);
        imeiInput.focus();
    });

    function hideImeiSuggest() {
        imeiBox.style.display = 'none';
        imeiBox.innerHTML = '';
        imeiItems = [];
        imeiActive = -1;
        imeiInput.setAttribute('aria-expanded', 'false');
    }

    function setActiveImei(idx, dir = 1) {
        const rows = imeiBox.querySelectorAll('[role="option"]');
        if (!rows.length) { imeiActive = -1; return; }
        const wrap = (n) => ((n % rows.length) + rows.length) % rows.length;
        let i = wrap(idx);
        for (let n = 0; n < rows.length && rows[i].disabled; n++) {
            i = wrap(i + dir);
        }
        if (rows[i].disabled) {
            imeiActive = -1;
            rows.forEach(el => el.classList.remove('is-active'));
            return;
        }
        imeiActive = i;
        rows.forEach((el, n) => {
            el.classList.toggle('is-active', n === imeiActive);
            el.setAttribute('aria-selected', n === imeiActive ? 'true' : 'false');
        });
        rows[imeiActive].scrollIntoView({ block: 'nearest' });
    }

    function renderImeiSuggest(list, taken, total) {
        imeiItems = list;
        imeiActive = -1;

        const head = `<div class="pos-imei-head">
            <span>Available units</span>
            <span>${total} in stock</span>
        </div>`;

        const body = list.length
            ? '<div class="pos-imei-list">' + list.map((code, i) => {
                const used = taken.includes(code);
                return `<button type="button" class="pos-imei-item${used ? ' is-used' : ''}" role="option"
                            data-idx="${i}" aria-selected="false" ${used ? 'disabled' : ''}>
                    <span class="pos-imei-code">
                        <span class="material-symbols-rounded">${used ? 'check_circle' : 'smartphone'}</span>
                        ${escHtml(code)}
                    </span>
                    <span class="badge-pill ${used ? 'badge-muted' : 'badge-success'}">${used ? 'Added' : 'Available'}</span>
                </button>`;
            }).join('') + '</div>'
            : '<div class="pos-imei-empty text-muted-2">No matching IMEI in this warehouse</div>';

        imeiBox.innerHTML = head + body;
        imeiBox.style.display = 'block';
        imeiInput.setAttribute('aria-expanded', 'true');
        if (list.length) setActiveImei(0);
    }

    async function refreshImeiSuggest() {
        if (!pendingProduct || !pendingProduct.has_imei) { hideImeiSuggest(); return; }
        const q = imeiInput.value.trim();
        if (imeiAbort) imeiAbort.abort();
        imeiAbort = new AbortController();
        try {
            const { ok, data } = await Nubia.ajax(
                '/api/products/' + pendingProduct.id + '/serials?limit=25'
                + '&warehouse=' + encodeURIComponent(warehouseSel.value)
                + '&q=' + encodeURIComponent(q),
                { signal: imeiAbort.signal }
            );
            if (!ok) return;
            const taken = selectedImeis.concat(cartImeis(pendingProduct.id));
            const list = (data.serials || [])
                .map(s => String(s.imei || s.serial_no || '').toUpperCase())
                .filter(Boolean);
            if (imeiHint) {
                imeiHint.textContent = q
                    ? 'Showing matches for "' + q + '"'
                    : 'Pick from the list below or scan to add.';
            }
            renderImeiSuggest(list, taken, Number(data.available) || 0);
        } catch (e) {
            if (e && e.name === 'AbortError') return;
        }
    }

    function pickImei(code) {
        if (selectedImeis.includes(code)) {
            Nubia.toast('IMEI already selected', 'warning');
            return;
        }
        if (cartImeis(pendingProduct.id).includes(code)) {
            Nubia.toast('IMEI already in the cart', 'warning');
            return;
        }
        if (pendingProduct.stock > 0 && selectedImeis.length >= pendingProduct.stock) {
            Nubia.toast('Only ' + pendingProduct.stock + ' in stock', 'warning');
            return;
        }
        selectedImeis.push(code);
        imeiInput.value = '';
        renderImeiChips();
        imeiInput.focus();
        refreshImeiSuggest();
    }

    imeiBox.addEventListener('mousedown', e => {
        const btn = e.target.closest('.pos-imei-item');
        if (!btn || btn.disabled) return;
        e.preventDefault();
        const code = imeiItems[parseInt(btn.dataset.idx, 10)];
        if (code) pickImei(code);
    });

    imeiInput?.addEventListener('input', () => {
        clearTimeout(imeiTimer);
        imeiTimer = setTimeout(refreshImeiSuggest, 180);
    });

    imeiInput?.addEventListener('keydown', e => {
        const open = imeiBox.style.display === 'block' && imeiItems.length > 0;
        if (e.key === 'ArrowDown' && open) {
            e.preventDefault();
            setActiveImei(imeiActive + 1);
            return;
        }
        if (e.key === 'ArrowUp' && open) {
            e.preventDefault();
            setActiveImei(imeiActive - 1, -1);
            return;
        }
        if (e.key === 'Escape' && open) {
            e.preventDefault();
            hideImeiSuggest();
            return;
        }
        // Backspace on an empty box removes the last chip.
        if (e.key === 'Backspace' && imeiInput.value === '' && selectedImeis.length) {
            e.preventDefault();
            removeImei(selectedImeis[selectedImeis.length - 1]);
            return;
        }
        if (e.key !== 'Enter') return;
        e.preventDefault();

        const typed = imeiInput.value.trim().toUpperCase();
        // Scanner wedge: an exact code wins over whatever row is highlighted.
        const exact = imeiItems.find(c => c === typed);
        if (exact) { pickImei(exact); return; }
        if (open && imeiActive >= 0) {
            pickImei(imeiItems[imeiActive]);
            return;
        }
        if (typed) Nubia.toast(typed + ' is not available in this warehouse', 'error');
    });

    imeiInput?.addEventListener('click', () => {
        clearTimeout(imeiTimer);
        imeiTimer = setTimeout(refreshImeiSuggest, 60);
    });

    qtyModalEl.addEventListener('hidden.bs.modal', () => {
        clearTimeout(imeiTimer);
        if (imeiAbort) imeiAbort.abort();
        hideImeiSuggest();
        selectedImeis = [];
        renderImeiChips();
        // Dismissed without adding → drop the reflected selection.
        if (pendingProduct) {
            pendingProduct = null;
            clearSearch({ focus: false });
        }
    });

    // Adjustable POS text size (saved in browser)
    const FS_KEY = 'nubia-pos-font-scale';
    const FS_MIN = 0.85;
    const FS_MAX = 1.35;
    const FS_STEP = 0.05;
    function applyFontScale(scale) {
        const s = Math.min(FS_MAX, Math.max(FS_MIN, scale));
        posLayout.style.setProperty('--pos-font-scale', s);
        localStorage.setItem(FS_KEY, String(s));
    }
    applyFontScale(parseFloat(localStorage.getItem(FS_KEY)) || 1);
    document.getElementById('posFontUp').addEventListener('click', () => {
        applyFontScale((parseFloat(posLayout.style.getPropertyValue('--pos-font-scale')) || 1) + FS_STEP);
    });
    document.getElementById('posFontDown').addEventListener('click', () => {
        applyFontScale((parseFloat(posLayout.style.getPropertyValue('--pos-font-scale')) || 1) - FS_STEP);
    });

    // Live search: grid filter + keyboard-navigable dropdown
    const resultsBox = document.getElementById('posSearchResults');
    let searchTimer = null;
    let searchAbort = null;
    let searchItems = [];
    let activeIdx = -1;

    function filterGrid(q) {
        const needle = q.toLowerCase();
        const tiles = grid.querySelectorAll('.pos-tile');
        let visible = 0;
        tiles.forEach(t => {
            const name = (t.dataset.name || '').toLowerCase();
            const sku = (t.dataset.sku || '').toLowerCase();
            const barcode = (t.dataset.barcode || '').toLowerCase();
            const match = !needle || name.includes(needle) || sku.includes(needle) || barcode.includes(needle);
            t.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const empty = document.getElementById('posEmpty');
        if (!visible) {
            empty.innerHTML = tiles.length === 0
                ? '<span class="material-symbols-rounded">inventory_2</span><p>No stock available in this warehouse.</p>'
                : '<span class="material-symbols-rounded">search_off</span><p>No products match your search.</p>';
        }
        empty.style.display = visible ? 'none' : '';
    }

    function hideSearchResults() {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        searchItems = [];
        activeIdx = -1;
        search.setAttribute('aria-expanded', 'false');
    }

    /** Show the ✕ button whenever the field holds text / a selection. */
    function updateSearchClear() {
        const has = search.value.trim() !== '';
        searchClear.hidden = !has;
        searchWrap.classList.toggle('has-selection', has);
    }

    function clearSearch({ focus = true } = {}) {
        search.value = '';
        updateSearchClear();
        hideSearchResults();
        filterGrid('');
        if (focus) search.focus();
    }

    searchClear.addEventListener('click', () => clearSearch());

    function setActiveResult(idx) {
        const links = resultsBox.querySelectorAll('[role="option"]');
        if (!links.length) { activeIdx = -1; return; }
        activeIdx = ((idx % links.length) + links.length) % links.length;
        links.forEach((el, i) => {
            el.classList.toggle('is-active', i === activeIdx);
            el.setAttribute('aria-selected', i === activeIdx ? 'true' : 'false');
        });
        links[activeIdx].scrollIntoView({ block: 'nearest' });
    }

    function selectSearchItem(p, { autoAdd = false } = {}) {
        if (!p) return;
        hideSearchResults();
        const product = productFromApi(p);
        if (product.matched_imei) {
            if (product.stock <= 0) {
                Nubia.toast(product.name + ' IMEI not available in this warehouse', 'error');
                clearSearch();
                return;
            }
            addToCart(product, 1, [product.matched_imei]);
            clearSearch();
            return;
        }
        if (autoAdd && !product.has_imei) {
            if (product.stock <= 0) {
                Nubia.toast(product.name + ' is out of stock', 'error');
                clearSearch();
                return;
            }
            addToCart(product, 1);
            clearSearch();
            return;
        }
        // Reflect the picked product in the field with a clear (✕) button.
        search.value = product.name;
        filterGrid(product.name);
        updateSearchClear();
        openQtyModal(product, 1);
    }

    async function lookupProducts(q) {
        const wh = document.getElementById('posWarehouse').value;
        const { ok, data } = await Nubia.ajax(
            '/api/products/search?q=' + encodeURIComponent(q) + '&warehouse=' + encodeURIComponent(wh)
        );
        if (!ok) return [];
        return data.results || [];
    }

    function findExactProduct(list, code) {
        const lower = String(code).toLowerCase();
        return (list || []).find(p =>
            String(p.barcode || '').toLowerCase() === lower
            || String(p.sku || '').toLowerCase() === lower
            || String(p.matched_imei || '').toLowerCase() === lower
            || String(p.imei || '').toLowerCase() === lower
        ) || null;
    }

    /** Used by USB/Bluetooth scanners and phone camera. */
    async function handleScannedCode(code, { autoAdd = true } = {}) {
        const q = String(code || '').trim();
        if (!q) return;
        try {
            const list = await lookupProducts(q);
            if (!list.length) {
                Nubia.toast('Barcode not found: ' + q, 'error');
                search.value = q;
                updateSearchClear();
                search.select();
                return;
            }
            const exact = findExactProduct(list, q);
            if (exact) {
                selectSearchItem(exact, { autoAdd });
                return;
            }
            if (list.length === 1) {
                selectSearchItem(list[0], { autoAdd });
                return;
            }
            search.value = q;
            updateSearchClear();
            filterGrid(q);
            renderSearchResults(list);
            search.focus();
        } catch (err) {
            Nubia.toast('Scan lookup failed', 'error');
        }
    }

    function renderSearchResults(list) {
        searchItems = list || [];
        activeIdx = -1;
        if (!searchItems.length) {
            resultsBox.innerHTML = '<div class="pos-search-empty text-muted-2">No products found</div>';
            resultsBox.style.display = 'block';
            search.setAttribute('aria-expanded', 'true');
            return;
        }
        resultsBox.innerHTML = searchItems.map((p, i) => {
            const stock = Math.floor(parseFloat(p.stock) || 0);
            const stockCls = stock > 0 ? 'badge-success' : 'badge-danger';
            const stockLabel = stock > 0 ? (stock + ' in stock') : 'Out';
            return `<button type="button" class="pos-search-item" role="option" data-idx="${i}" aria-selected="false">
                <span class="pos-search-item-main">
                    <strong>${escHtml(p.name)}</strong>
                    <span class="text-muted-2">${escHtml(p.sku || p.barcode || '')}</span>
                </span>
                <span class="pos-search-item-meta">
                    <span class="fw-800">${Nubia.fmtMoney(p.selling_price)}</span>
                    <span class="badge-pill ${stockCls}">${stockLabel}</span>
                </span>
            </button>`;
        }).join('');
        resultsBox.style.display = 'block';
        search.setAttribute('aria-expanded', 'true');
        setActiveResult(0);
    }

    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    async function fetchSearchResults(q) {
        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();
        try {
            const wh = document.getElementById('posWarehouse').value;
            const { ok, data } = await Nubia.ajax(
                '/api/products/search?q=' + encodeURIComponent(q) + '&warehouse=' + encodeURIComponent(wh),
                { signal: searchAbort.signal }
            );
            if (!ok) return;
            const lower = q.toLowerCase();
            let list = data.results || [];
            list = list.slice().sort((a, b) => {
                const aExact = (String(a.barcode || '').toLowerCase() === lower || String(a.sku || '').toLowerCase() === lower) ? 0 : 1;
                const bExact = (String(b.barcode || '').toLowerCase() === lower || String(b.sku || '').toLowerCase() === lower) ? 0 : 1;
                return aExact - bExact;
            });

            // Exact barcode/SKU while typing from a wedge scanner → add immediately
            if (scannerBurst && list.length) {
                const exact = findExactProduct(list, q);
                if (exact) {
                    scannerBurst = false;
                    selectSearchItem(exact, { autoAdd: true });
                    return;
                }
            }
            renderSearchResults(list);
        } catch (e) {
            if (e && e.name === 'AbortError') return;
        }
    }

    // Hardware scanner: rapid key bursts (USB/Bluetooth wedge)
    let lastScanKeyAt = 0;
    let scannerBurst = false;
    search.addEventListener('keydown', function(e) {
        const now = Date.now();
        const gap = now - lastScanKeyAt;
        lastScanKeyAt = now;

        if (e.key.length === 1) {
            scannerBurst = gap > 0 && gap < 55;
        }

        const open = resultsBox.style.display === 'block' && searchItems.length > 0;

        if (e.key === 'ArrowDown') {
            if (!open) return;
            e.preventDefault();
            setActiveResult(activeIdx + 1);
            return;
        }
        if (e.key === 'ArrowUp') {
            if (!open) return;
            e.preventDefault();
            setActiveResult(activeIdx - 1);
            return;
        }
        if (e.key === 'Escape') {
            if (resultsBox.style.display === 'block') {
                e.preventDefault();
                hideSearchResults();
            }
            return;
        }
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const q = this.value.trim();
        if (!q) return;

        const treatAsScan = scannerBurst || q.length >= 6;
        scannerBurst = false;

        if (!treatAsScan && open && activeIdx >= 0 && searchItems[activeIdx]) {
            selectSearchItem(searchItems[activeIdx], { autoAdd: false });
            return;
        }

        handleScannedCode(q, { autoAdd: true });
    });

    search.addEventListener('input', function() {
        const q = this.value.trim();
        filterGrid(q);
        updateSearchClear();
        clearTimeout(searchTimer);
        if (!q) {
            hideSearchResults();
            return;
        }
        // Scanners finish with Enter; keep debounce short for live search
        searchTimer = setTimeout(() => fetchSearchResults(q), scannerBurst ? 40 : 180);
    });

    resultsBox.addEventListener('mousedown', e => {
        const item = e.target.closest('.pos-search-item');
        if (!item) return;
        e.preventDefault();
        const idx = parseInt(item.dataset.idx, 10);
        if (searchItems[idx]) selectSearchItem(searchItems[idx], { autoAdd: false });
    });

    resultsBox.addEventListener('mousemove', e => {
        const item = e.target.closest('.pos-search-item');
        if (!item) return;
        const idx = parseInt(item.dataset.idx, 10);
        if (!isNaN(idx) && idx !== activeIdx) setActiveResult(idx);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.pos-search-wrap')) hideSearchResults();
    });

    // Checkout
    document.getElementById('posCheckout')?.addEventListener('click', async function() {
        if (!cart.length) { Nubia.toast('Cart is empty', 'error'); return; }
        const total = totals();
        const paidRaw = document.getElementById('posPaid').value.trim();
        const paid = paidRaw === '' ? total : (parseFloat(paidRaw) || 0);
        const due = Math.max(0, total - paid);
        const customerId = document.getElementById('posCustomer').value;
        const method = document.getElementById('posMethod').value;

        if ((due > 0 || method === 'credit') && !customerId) {
            Nubia.toast('Select a registered customer for due/credit sales. Walk-in must pay in full.', 'error');
            document.getElementById('posCustomer').focus();
            return;
        }

        this.disabled = true;
        const body = new URLSearchParams({
            _token: CSRF_TOKEN,
            items: JSON.stringify(cart),
            customer_id: customerId,
            warehouse_id: document.getElementById('posWarehouse').value,
            discount: document.getElementById('posDiscount').value || 0,
            discount_type: document.getElementById('posDiscountType').value,
            tax: 0,
            paid: paid,
            payment_method: method
        });
        try {
            const res = await fetch(NUBIA_BASE + '/pos/checkout', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
            const data = await res.json();
            if (data.success) {
                const dueHtml = data.sale.due > 0 ? `<br>Due: ${fmtMoneyText(data.sale.due)}` : '';
                Swal.fire({
                    icon: 'success', title: 'Sale Completed!',
                    html: `Invoice <b>${data.sale.invoice_no}</b><br>Total: ${fmtMoneyText(data.sale.total)}<br>Change: ${fmtMoneyText(data.sale.change)}${dueHtml}`,
                    showCancelButton: true, confirmButtonText: 'Print Receipt', cancelButtonText: 'New Sale', confirmButtonColor: '#dc2626'
                }).then(r => { if (r.isConfirmed) window.open(data.thermal_url, '_blank'); });
                clearCart();
                document.getElementById('posPaid').value = '';
                document.getElementById('posDiscount').value = '';
                document.getElementById('posDiscountType').value = 'fixed';
                // Stock changed server-side — pull fresh counts for the grid.
                await loadGrid();
                search.focus();
            } else {
                Nubia.toast(data.message || 'Checkout failed', 'error');
            }
        } catch (err) {
            Nubia.toast('Checkout error', 'error');
        }
        this.disabled = false;
    });

    // -------- Phone camera barcode scanner --------
    const scanModalEl = document.getElementById('posScanModal');
    const scanModal = scanModalEl ? new bootstrap.Modal(scanModalEl) : null;
    const scanStatus = document.getElementById('posScanStatus');
    let html5Qrcode = null;
    let scanBusy = false;

    async function ensureHtml5Qrcode() {
        if (window.Html5Qrcode) return;
        await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
            s.onload = resolve;
            s.onerror = () => reject(new Error('Failed to load scanner library'));
            document.head.appendChild(s);
        });
    }

    async function startCameraScan() {
        if (!scanModal) return;
        scanBusy = false;
        if (scanStatus) scanStatus.textContent = 'Starting camera…';
        scanModal.show();
        try {
            await ensureHtml5Qrcode();
            if (html5Qrcode) {
                try { await html5Qrcode.stop(); } catch (e) {}
                try { await html5Qrcode.clear(); } catch (e) {}
            }
            html5Qrcode = new Html5Qrcode('posScanReader');
            const cameras = await Html5Qrcode.getCameras();
            if (!cameras || !cameras.length) {
                if (scanStatus) scanStatus.textContent = 'No camera found on this device.';
                return;
            }
            // Prefer back camera on phones
            let cameraId = cameras[0].id;
            const back = cameras.find(c => /back|rear|environment/i.test(c.label || ''));
            if (back) cameraId = back.id;

            await html5Qrcode.start(
                cameraId,
                { fps: 10, qrbox: { width: 260, height: 160 }, aspectRatio: 1.777 },
                async (decoded) => {
                    if (scanBusy) return;
                    scanBusy = true;
                    const code = String(decoded || '').trim();
                    if (scanStatus) scanStatus.textContent = 'Found: ' + code;
                    try { await html5Qrcode.stop(); } catch (e) {}
                    scanModal.hide();
                    await handleScannedCode(code, { autoAdd: true });
                    scanBusy = false;
                },
                () => {}
            );
            if (scanStatus) scanStatus.textContent = 'Align the barcode inside the frame';
        } catch (err) {
            console.error(err);
            if (scanStatus) {
                scanStatus.textContent = 'Camera permission needed. Allow camera access, or use a USB scanner in the search box.';
            }
            Nubia.toast('Could not open camera. Use the search box with a barcode scanner instead.', 'warning');
        }
    }

    async function stopCameraScan() {
        if (!html5Qrcode) return;
        try { await html5Qrcode.stop(); } catch (e) {}
        try { await html5Qrcode.clear(); } catch (e) {}
        html5Qrcode = null;
    }

    document.getElementById('posCameraScanBtn')?.addEventListener('click', () => startCameraScan());
    scanModalEl?.addEventListener('hidden.bs.modal', () => { stopCameraScan(); });

    // Keep focus on search for hardware scanners (unless typing elsewhere / modal open)
    document.addEventListener('keydown', e => {
        if (e.key === 'F2') {
            e.preventDefault();
            search.focus();
            search.select();
            return;
        }
        if (e.key === 'F3') {
            e.preventDefault();
            startCameraScan();
            return;
        }
        const tag = (e.target && e.target.tagName || '').toLowerCase();
        const typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable;
        const modalOpen = document.querySelector('.modal.show');
        if (!typing && !modalOpen && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            search.focus();
        }
    });
})();
JS; ?>
