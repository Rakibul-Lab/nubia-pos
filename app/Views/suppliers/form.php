<?php
use App\Core\View;
/** @var array|null $supplier */
$isEdit = $supplier !== null;
$action = $isEdit ? url('suppliers/' . $supplier['id']) : url('suppliers');
$val = static fn (string $k, $d = '') => e((string) ($supplier[$k] ?? $d));
?>
<?= View::partial('components.page_head', ['title' => $isEdit ? 'Edit Supplier' : 'New Supplier']) ?>

<form method="POST" action="<?= $action ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card"><div class="card-header">Supplier Details</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Company</label><input type="text" name="company" class="form-control" value="<?= $val('company') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= $val('phone') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= $val('email') ?>"></div>
                    <div class="col-md-8"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= $val('address') ?>"></div>
                    <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= $val('city') ?>"></div>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-header">Account</div><div class="card-body">
                <div class="mb-1"><label class="form-label">Opening Balance (Payable)</label><input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= $val('opening_balance', '0') ?>"></div>
            </div></div>
            <div class="d-grid gap-2"><button class="btn btn-brand py-2"><span class="material-symbols-rounded">save</span> Save Supplier</button><a href="<?= url('suppliers') ?>" class="btn btn-soft">Cancel</a></div>
        </div>
    </div>
</form>
