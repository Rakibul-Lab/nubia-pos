<?php
use App\Core\View;
/** @var array|null $user @var array $roles @var array $warehouses @var bool $canAssignRoles @var bool $canAssignWarehouses @var string $currentRoleName */
$isEdit = $user !== null;
$action = $isEdit ? url('users/' . $user['id']) : url('users');
$val = static fn (string $k, $d = '') => e((string) ($user[$k] ?? $d));
?>
<?= View::partial('components.page_head', ['title' => $isEdit ? 'Edit User' : 'New User']) ?>
<form method="POST" action="<?= $action ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card"><div class="card-header">User Details</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="<?= $val('email') ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= $val('phone') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Password <?= $isEdit ? '(leave blank to keep)' : '*' ?></label><input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>></div>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-header">Access</div><div class="card-body">
                <?php if ($canAssignRoles): ?>
                    <div class="mb-3"><label class="form-label">Role *</label><select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= (int) ($user['role_id'] ?? 0) === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
                    </select></div>
                <?php else: ?>
                    <div class="mb-3"><label class="form-label">Role</label><div class="form-control bg-body-secondary"><?= e($currentRoleName) ?></div></div>
                <?php endif; ?>
                <?php if ($canAssignWarehouses): ?>
                    <div class="mb-3"><label class="form-label">Warehouse</label><select name="warehouse_id" class="form-select"><option value="">All</option>
                        <?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>" <?= (int) ($user['warehouse_id'] ?? 0) === (int) $w['id'] ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?>
                    </select></div>
                <?php endif; ?>
                <div class="mb-1"><label class="form-label">Status</label><select name="status" class="form-select">
                    <?php foreach (['active', 'inactive', 'suspended'] as $st): ?><option value="<?= $st ?>" <?= ($user['status'] ?? 'active') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
                </select></div>
            </div></div>
            <div class="d-grid gap-2"><button class="btn btn-brand py-2"><span class="material-symbols-rounded">save</span> Save User</button><a href="<?= url('users') ?>" class="btn btn-soft">Cancel</a></div>
        </div>
    </div>
</form>
