<?php
use App\Core\View;
/** @var array $units */
$canCreate = can('units.create');
$canEdit = can('units.edit');
$canDelete = can('units.delete');
?>
<?= View::partial('components.page_head', [
    'title'    => 'Units',
    'subtitle' => 'Measurement units for products',
    'extraActions' => $canCreate ? '<button class="btn btn-brand" onclick="openUnitModal()"><span class="material-symbols-rounded">add</span> Add Unit</button>' : '',
]) ?>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Name</th><th>Short Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$units): ?><tr><td colspan="4"><?= View::partial('components.empty', ['icon' => 'straighten', 'title' => 'No units yet']) ?></td></tr><?php endif; ?>
            <?php foreach ($units as $u): ?>
                <tr>
                    <td class="fw-800"><?= e($u['name']) ?></td>
                    <td><span class="badge-pill badge-muted"><?= e($u['short_name']) ?></span></td>
                    <td><span class="badge-pill <?= $u['status'] ? 'badge-success' : 'badge-muted' ?>"><?= $u['status'] ? 'Active' : 'Inactive' ?></span></td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1">
                            <?php if ($canEdit): ?><button type="button" class="icon-btn" style="width:34px;height:34px;" title="Edit" onclick='editUnit(<?= json_encode(["id" => $u["id"], "name" => $u["name"], "short_name" => $u["short_name"]]) ?>)'><span class="material-symbols-rounded" style="font-size:18px;">edit</span></button><?php endif; ?>
                            <?php if ($canDelete): ?><form method="POST" action="<?= url('units/' . $u['id']) ?>" class="m-0"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="button" class="icon-btn" style="width:34px;height:34px;" title="Delete" data-confirm-delete="Delete this unit?"><span class="material-symbols-rounded" style="font-size:18px;color:var(--danger);">delete</span></button></form><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canCreate || $canEdit): ?><div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="unitForm" action="<?= url('units') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title" id="unitTitle">Add Unit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="unitName" class="form-control" required></div>
                    <div class="mb-1"><label class="form-label">Short Name *</label><input type="text" name="short_name" id="unitShort" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
            </form>
        </div>
    </div>
</div><?php endif; ?>

<?php if ($canCreate || $canEdit) {
    $pageScript = <<<JS
const unitModal = new bootstrap.Modal('#unitModal');
function openUnitModal(){ document.getElementById('unitForm').action = NUBIA_BASE + '/units'; document.getElementById('unitTitle').textContent='Add Unit'; document.getElementById('unitName').value=''; document.getElementById('unitShort').value=''; unitModal.show(); }
function editUnit(u){ document.getElementById('unitForm').action = NUBIA_BASE + '/units/' + u.id; document.getElementById('unitTitle').textContent='Edit Unit'; document.getElementById('unitName').value=u.name; document.getElementById('unitShort').value=u.short_name; unitModal.show(); }
JS;
} ?>
