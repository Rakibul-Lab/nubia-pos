<?php
use App\Core\View;
/** @var array $users */
?>
<?= View::partial('components.page_head', ['title' => 'Users', 'subtitle' => count($users) . ' team members', 'actionUrl' => can('users.create') ? url('users/create') : null, 'actionLabel' => 'Add User']) ?>
<div class="card"><div class="table-wrap"><table class="nubia">
    <thead><tr><th>User</th><th>Role</th><th>Phone</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><div class="d-flex align-items-center gap-2"><div class="avatar sm"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></div><div><div class="fw-800"><?= e($u['name']) ?></div><div class="text-muted-2" style="font-size:.75rem;"><?= e($u['email']) ?></div></div></div></td>
            <td><span class="badge-pill badge-info"><?= e($u['role_name'] ?? '—') ?></span></td>
            <td><?= e($u['phone'] ?? '—') ?></td>
            <td><span class="badge-pill <?= $u['status'] === 'active' ? 'badge-success' : 'badge-muted' ?>"><?= ucfirst($u['status']) ?></span></td>
            <td class="text-muted-2" style="font-size:.82rem;"><?= $u['last_login_at'] ? date('M j, g:i A', strtotime($u['last_login_at'])) : 'Never' ?></td>
            <td class="text-end">
                <?php $canManageThisUser = ($u['role_slug'] ?? '') !== 'super-admin' || can('users.assign_super_admin'); ?>
                <div class="table-actions">
                    <?php if ($canManageThisUser && can('users.edit')): ?>
                        <a href="<?= url('users/' . $u['id'] . '/edit') ?>" class="icon-btn" style="width:34px;height:34px;" title="Edit"><span class="material-symbols-rounded" style="font-size:18px;">edit</span></a>
                    <?php endif; ?>
                    <?php if ($canManageThisUser && can('users.delete')): ?>
                        <form method="POST" action="<?= url('users/' . $u['id']) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="icon-btn" style="width:34px;height:34px;" data-confirm-delete="Delete user?" title="Delete"><span class="material-symbols-rounded" style="font-size:18px;color:var(--danger);">delete</span></button>
                        </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>
