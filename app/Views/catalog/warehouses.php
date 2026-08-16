<?php
use App\Core\View;
/** @var array $warehouses */
$canCreate = can('warehouses.create');
$canEdit = can('warehouses.edit');
$canDelete = can('warehouses.delete');
?>
<?= View::partial('components.page_head', [
    'title'    => 'Warehouses',
    'subtitle' => 'Manage multiple stock locations',
    'extraActions' => $canCreate ? '<button class="btn btn-brand" onclick="openWhModal()"><span class="material-symbols-rounded">add</span> Add Warehouse</button>' : '',
]) ?>

<div class="row g-3">
    <?php if (!$warehouses): ?>
        <div class="col-12"><div class="card"><?= View::partial('components.empty', ['icon' => 'warehouse', 'title' => 'No warehouses']) ?></div></div>
    <?php endif; ?>
    <?php foreach ($warehouses as $w): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="stat-icon bg-grad-1" style="width:44px;height:44px;"><span class="material-symbols-rounded">warehouse</span></div>
                        <?php if ($w['is_default']): ?><span class="badge-pill badge-success">Default</span><?php endif; ?>
                    </div>
                    <h5 class="fw-800 mt-3 mb-1"><?= e($w['name']) ?></h5>
                    <div class="text-muted-2" style="font-size:.82rem;">
                        <div><span class="material-symbols-rounded" style="font-size:15px;">tag</span> <?= e($w['code']) ?></div>
                        <?php if ($w['phone']): ?><div><span class="material-symbols-rounded" style="font-size:15px;">call</span> <?= e($w['phone']) ?></div><?php endif; ?>
                        <?php if ($w['address']): ?><div><span class="material-symbols-rounded" style="font-size:15px;">location_on</span> <?= e($w['address']) ?></div><?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <?php if ($canEdit): ?><button class="btn btn-soft btn-sm flex-grow-1" onclick='editWh(<?= json_encode(["id" => $w["id"], "name" => $w["name"], "phone" => $w["phone"], "address" => $w["address"]]) ?>)'><span class="material-symbols-rounded" style="font-size:17px;">edit</span> Edit</button><?php endif; ?>
                        <?php if ($canDelete): ?><form method="POST" action="<?= url('warehouses/' . $w['id']) ?>"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="button" class="btn btn-danger-soft btn-sm" data-confirm-delete="Delete warehouse?"><span class="material-symbols-rounded" style="font-size:17px;">delete</span></button></form><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($canCreate || $canEdit): ?><div class="modal fade" id="whModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="whForm" action="<?= url('warehouses') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title" id="whTitle">Add Warehouse</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="whName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" id="whPhone" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Address</label><input type="text" name="address" id="whAddress" class="form-control"></div>
                    <div class="form-check form-switch" id="whDefaultWrap"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="whDefault"><label class="form-check-label" for="whDefault">Set as default</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
            </form>
        </div>
    </div>
</div><?php endif; ?>

<?php if ($canCreate || $canEdit) {
    $pageScript = <<<JS
const whModal = new bootstrap.Modal('#whModal');
function openWhModal(){ const f=document.getElementById('whForm'); f.action=NUBIA_BASE+'/warehouses'; document.getElementById('whTitle').textContent='Add Warehouse'; ['whName','whPhone','whAddress'].forEach(i=>document.getElementById(i).value=''); document.getElementById('whDefaultWrap').style.display=''; whModal.show(); }
function editWh(w){ const f=document.getElementById('whForm'); f.action=NUBIA_BASE+'/warehouses/'+w.id; document.getElementById('whTitle').textContent='Edit Warehouse'; document.getElementById('whName').value=w.name; document.getElementById('whPhone').value=w.phone||''; document.getElementById('whAddress').value=w.address||''; document.getElementById('whDefaultWrap').style.display='none'; whModal.show(); }
JS;
} ?>
