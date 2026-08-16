<?php
use App\Core\View;
/** @var array $roles */
$canCreate = can('roles.create');
$canAssign = can('roles.permissions');
$canDelete = can('roles.delete');
?>
<?= View::partial('components.page_head', ['title' => 'Roles & Permissions', 'subtitle' => 'Control what each role can access', 'extraActions' => $canCreate ? '<button class="btn btn-brand" onclick="roleModal.show()"><span class="material-symbols-rounded">add</span> Add Role</button>' : '']) ?>
<div class="row g-3">
    <?php foreach ($roles as $r): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="stat-icon bg-grad-1" style="width:44px;height:44px;"><span class="material-symbols-rounded">admin_panel_settings</span></div>
                    <?php if ($r['is_system']): ?><span class="badge-pill badge-muted">System</span><?php endif; ?>
                </div>
                <h5 class="fw-800 mt-3 mb-1"><?= e($r['name']) ?></h5>
                <p class="text-muted-2" style="font-size:.82rem;"><?= e($r['description'] ?? 'No description') ?></p>
                <div class="d-flex gap-3 mb-3" style="font-size:.82rem;">
                    <span><span class="fw-800"><?= (int) $r['user_count'] ?></span> users</span>
                    <span><span class="fw-800"><?= $r['slug'] === 'super-admin' ? 'All' : (int) $r['permission_count'] ?></span> permissions</span>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($canAssign): ?><a href="<?= url('roles/' . $r['id'] . '/permissions') ?>" class="btn btn-soft btn-sm flex-grow-1"><span class="material-symbols-rounded" style="font-size:17px;">tune</span> Permissions</a><?php endif; ?>
                    <?php if ($canDelete && !$r['is_system']): ?><form method="POST" action="<?= url('roles/' . $r['id']) ?>"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="button" class="btn btn-danger-soft btn-sm" data-confirm-delete="Delete role?"><span class="material-symbols-rounded" style="font-size:17px;">delete</span></button></form><?php endif; ?>
                </div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($canCreate): ?><div class="modal fade" id="roleModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="POST" action="<?= url('roles') ?>"><?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Add Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-1"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Create</button></div>
    </form>
</div></div></div>
<?php $pageScript = "const roleModal = new bootstrap.Modal('#roleModal');"; endif; ?>
