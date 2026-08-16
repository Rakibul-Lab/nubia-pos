<?php
use App\Core\View;
/** @var array $categories @var array $parents */
$canCreate = can('categories.create');
$canEdit = can('categories.edit');
$canDelete = can('categories.delete');
?>
<?= View::partial('components.page_head', [
    'title'    => 'Categories',
    'subtitle' => 'Organize products into categories & sub-categories',
    'extraActions' => $canCreate ? '<button class="btn btn-brand" onclick="openCatModal()"><span class="material-symbols-rounded">add</span> Add Category</button>' : '',
]) ?>

<div class="card">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Name</th><th>Parent</th><th>Products</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$categories): ?><tr><td colspan="5"><?= View::partial('components.empty', ['icon' => 'category', 'title' => 'No categories yet']) ?></td></tr><?php endif; ?>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="fw-800"><?= e($c['name']) ?></td>
                    <td><?= e($c['parent_name'] ?? '—') ?></td>
                    <td><span class="badge-pill badge-info"><?= (int) $c['product_count'] ?></span></td>
                    <td><span class="badge-pill <?= $c['status'] ? 'badge-success' : 'badge-muted' ?>"><?= $c['status'] ? 'Active' : 'Inactive' ?></span></td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1">
                            <?php if ($canEdit): ?><button type="button" class="icon-btn" style="width:34px;height:34px;" title="Edit" onclick='editCat(<?= json_encode(["id" => $c["id"], "name" => $c["name"], "parent_id" => $c["parent_id"]]) ?>)'><span class="material-symbols-rounded" style="font-size:18px;">edit</span></button><?php endif; ?>
                            <?php if ($canDelete): ?><form method="POST" action="<?= url('categories/' . $c['id']) ?>" class="m-0"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="button" class="icon-btn" style="width:34px;height:34px;" title="Delete" data-confirm-delete="Delete this category?"><span class="material-symbols-rounded" style="font-size:18px;color:var(--danger);">delete</span></button></form><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canCreate || $canEdit): ?><div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="catForm" action="<?= url('categories') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title" id="catTitle">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="catName" class="form-control" required></div>
                    <div class="mb-1"><label class="form-label">Parent Category</label>
                        <select name="parent_id" id="catParent" class="form-select"><option value="">— None (Top level) —</option>
                            <?php foreach ($parents as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
            </form>
        </div>
    </div>
</div><?php endif; ?>

<?php if ($canCreate || $canEdit) {
    $pageScript = <<<JS
const catModal = new bootstrap.Modal('#catModal');
function openCatModal(){ document.getElementById('catForm').action = NUBIA_BASE + '/categories'; document.getElementById('catTitle').textContent='Add Category'; document.getElementById('catName').value=''; document.getElementById('catParent').value=''; catModal.show(); }
function editCat(c){ document.getElementById('catForm').action = NUBIA_BASE + '/categories/' + c.id; document.getElementById('catTitle').textContent='Edit Category'; document.getElementById('catName').value=c.name; document.getElementById('catParent').value=c.parent_id||''; catModal.show(); }
JS;
} ?>
