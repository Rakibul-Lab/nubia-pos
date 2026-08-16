<?php
use App\Core\View;
/** @var array $brands */
$canCreate = can('brands.create');
$canEdit = can('brands.edit');
$canDelete = can('brands.delete');
?>
<?= View::partial('components.page_head', [
    'title'    => 'Brands',
    'subtitle' => 'Manage product brands',
    'extraActions' => $canCreate ? '<button class="btn btn-brand" onclick="openBrandModal()"><span class="material-symbols-rounded">add</span> Add Brand</button>' : '',
]) ?>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$brands): ?><tr><td colspan="4"><?= View::partial('components.empty', ['icon' => 'sell', 'title' => 'No brands yet']) ?></td></tr><?php endif; ?>
            <?php foreach ($brands as $b): ?>
                <tr>
                    <td class="fw-800"><?= e($b['name']) ?></td>
                    <td class="text-muted-2"><?= e($b['slug']) ?></td>
                    <td><span class="badge-pill <?= $b['status'] ? 'badge-success' : 'badge-muted' ?>"><?= $b['status'] ? 'Active' : 'Inactive' ?></span></td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1">
                            <?php if ($canEdit): ?><button type="button" class="icon-btn" style="width:34px;height:34px;" title="Edit" onclick='editBrand(<?= json_encode(["id" => $b["id"], "name" => $b["name"]]) ?>)'><span class="material-symbols-rounded" style="font-size:18px;">edit</span></button><?php endif; ?>
                            <?php if ($canDelete): ?><form method="POST" action="<?= url('brands/' . $b['id']) ?>" class="m-0"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="button" class="icon-btn" style="width:34px;height:34px;" title="Delete" data-confirm-delete="Delete this brand?"><span class="material-symbols-rounded" style="font-size:18px;color:var(--danger);">delete</span></button></form><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canCreate || $canEdit): ?><div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="brandForm" action="<?= url('brands') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title" id="brandTitle">Add Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="mb-1"><label class="form-label">Name *</label><input type="text" name="name" id="brandName" class="form-control" required></div></div>
                <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
            </form>
        </div>
    </div>
</div><?php endif; ?>

<?php if ($canCreate || $canEdit) {
    $pageScript = <<<JS
const brandModal = new bootstrap.Modal('#brandModal');
function openBrandModal(){ document.getElementById('brandForm').action = NUBIA_BASE + '/brands'; document.getElementById('brandTitle').textContent='Add Brand'; document.getElementById('brandName').value=''; brandModal.show(); }
function editBrand(b){ document.getElementById('brandForm').action = NUBIA_BASE + '/brands/' + b.id; document.getElementById('brandTitle').textContent='Edit Brand'; document.getElementById('brandName').value=b.name; brandModal.show(); }
JS;
} ?>
