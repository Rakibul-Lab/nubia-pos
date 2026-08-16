<?php
use App\Core\View;
/** @var array $settings */
$s = static fn (string $k, $d = '') => e((string) ($settings[$k] ?? $d));
?>
<?= View::partial('components.page_head', ['title' => 'Settings', 'subtitle' => 'Configure your business, invoices and preferences']) ?>

<form method="POST" action="<?= url('settings') ?>">
    <?= csrf_field() ?>
    <fieldset <?= can('settings.edit') ? '' : 'disabled' ?>>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card mb-3"><div class="card-header"><span class="material-symbols-rounded">store</span> Business Information</div><div class="card-body">
                <div class="mb-3"><label class="form-label">Business Name</label><input type="text" name="business_name" class="form-control" value="<?= $s('business_name') ?>"></div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="business_email" class="form-control" value="<?= $s('business_email') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="business_phone" class="form-control" value="<?= $s('business_phone') ?>"></div>
                </div>
                <div class="mb-1"><label class="form-label">Address</label><textarea name="business_address" class="form-control" rows="2"><?= $s('business_address') ?></textarea></div>
            </div></div>

            <div class="card"><div class="card-header"><span class="material-symbols-rounded">receipt_long</span> Invoice &amp; Tax</div><div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6 mb-3"><label class="form-label">Invoice Prefix</label><input type="text" name="invoice_prefix" class="form-control" value="<?= $s('invoice_prefix', 'INV') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Purchase Prefix</label><input type="text" name="purchase_prefix" class="form-control" value="<?= $s('purchase_prefix', 'PUR') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Default Tax Rate (%)</label><input type="number" step="0.01" name="tax_rate" class="form-control" value="<?= $s('tax_rate', '0') ?>"></div>
                    <div class="col-md-6 mb-1"><label class="form-label">VAT Rate (%)</label><input type="number" step="0.01" name="vat_rate" class="form-control" value="<?= $s('vat_rate', '0') ?>"></div>
                </div>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3"><div class="card-header"><span class="material-symbols-rounded">tune</span> Localization</div><div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6 mb-3"><label class="form-label">Currency Code</label><input type="text" name="currency" class="form-control" value="<?= $s('currency', 'BDT') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" value="<?= $s('currency_symbol', 'Tk') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Timezone</label><input type="text" name="timezone" class="form-control" value="<?= $s('timezone', 'Asia/Dhaka') ?>"></div>
                    <div class="col-md-6 mb-1"><label class="form-label">Language</label>
                        <select name="language" class="form-select">
                            <option value="en" <?= $s('language') === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="bn" <?= $s('language') === 'bn' ? 'selected' : '' ?>>বাংলা (Bangla)</option>
                        </select>
                    </div>
                </div>
            </div></div>

            <div class="card mb-3"><div class="card-header"><span class="material-symbols-rounded">palette</span> Appearance</div><div class="card-body">
                <p class="text-muted-2 mb-2" style="font-size:.85rem;">Toggle dark / light mode from the top bar. Your preference is saved automatically.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-soft" onclick="Nubia.theme.set('light')"><span class="material-symbols-rounded">light_mode</span> Light</button>
                    <button type="button" class="btn btn-soft" onclick="Nubia.theme.set('dark')"><span class="material-symbols-rounded">dark_mode</span> Dark</button>
                </div>
            </div></div>

            <?php if (can('settings.edit')): ?><div class="d-grid"><button class="btn btn-brand py-2"><span class="material-symbols-rounded">save</span> Save Settings</button></div><?php endif; ?>
        </div>
    </div>
    </fieldset>
</form>
