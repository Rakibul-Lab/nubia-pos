<?php
use App\Core\View;
/** @var array $role @var array<string,array{icon:string,hint:string,items:array}> $grouped @var int[] $assigned @var bool $immutable */
$immutable = $immutable ?? false;
?>
<?= View::partial('components.page_head', [
    'title'    => 'Permissions',
    'subtitle' => e($role['name']) . ' · Match access to sidebar menus',
    'backUrl'  => url('roles'),
]) ?>

<form method="POST" action="<?= url('roles/' . $role['id'] . '/permissions') ?>" id="permForm">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-muted-2" style="font-size:.85rem;">
                Every checkbox controls one exact page or action. Grant view/search permissions together with create actions when a form needs record lookup.
            </div>
            <?php if (!$immutable): ?><div class="d-flex gap-2">
                <button type="button" class="btn btn-soft btn-sm" id="permSelectAll">Select All</button>
                <button type="button" class="btn btn-soft btn-sm" id="permClearAll">Clear All</button>
            </div><?php else: ?><span class="badge-pill badge-success">Unrestricted access</span><?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($grouped as $section => $block): ?>
            <?php
            $items = $block['items'] ?? [];
            $ids = array_map(static fn ($p) => (int) $p['id'], $items);
            $checkedCount = count(array_filter($ids, static fn ($id) => in_array($id, $assigned, true)));
            $allChecked = $items !== [] && $checkedCount === count($items);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 perm-section" data-section="<?= e($section) ?>">
                    <div class="card-header d-flex align-items-start justify-content-between gap-2">
                        <div class="d-flex align-items-start gap-2 min-w-0">
                            <span class="perm-section-icon"><span class="material-symbols-rounded"><?= e($block['icon'] ?? 'folder') ?></span></span>
                            <div class="min-w-0">
                                <div class="fw-800" style="line-height:1.2;"><?= e($section) ?></div>
                                <?php if (!empty($block['hint'])): ?>
                                    <div class="text-muted-2" style="font-size:.72rem;margin-top:2px;"><?= e($block['hint']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <label class="perm-section-toggle mb-0" title="Toggle entire section">
                            <input type="checkbox" class="form-check-input perm-section-cb m-0" <?= $allChecked ? 'checked' : '' ?> <?= $immutable ? 'disabled' : '' ?> aria-label="Select all <?= e($section) ?>">
                        </label>
                    </div>
                    <div class="card-body pt-2">
                        <?php foreach ($items as $p): ?>
                            <label class="perm-item">
                                <input type="checkbox" class="form-check-input perm-cb m-0" name="permissions[]" value="<?= (int) $p['id'] ?>" <?= in_array((int) $p['id'], $assigned, true) ? 'checked' : '' ?> <?= $immutable ? 'disabled' : '' ?>>
                                <span><?= e($p['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer py-2 px-3 text-muted-2" style="font-size:.72rem;">
                        <span class="perm-section-count"><?= $checkedCount ?></span>/<?= count($items) ?> selected
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$immutable): ?><div class="d-flex gap-2 mt-3 sticky-perm-actions">
        <button class="btn btn-brand"><span class="material-symbols-rounded">save</span> Save Permissions</button>
        <a href="<?= url('roles') ?>" class="btn btn-soft">Cancel</a>
    </div><?php endif; ?>
</form>

<?php $pageScript = <<<'JS'
(function () {
    function refreshSection(section) {
        const boxes = section.querySelectorAll('.perm-cb');
        const master = section.querySelector('.perm-section-cb');
        const countEl = section.querySelector('.perm-section-count');
        let checked = 0;
        boxes.forEach(cb => { if (cb.checked) checked++; });
        if (master) {
            master.checked = boxes.length > 0 && checked === boxes.length;
            master.indeterminate = checked > 0 && checked < boxes.length;
        }
        if (countEl) countEl.textContent = String(checked);
    }

    document.querySelectorAll('.perm-section').forEach(section => {
        refreshSection(section);
        section.querySelector('.perm-section-cb')?.addEventListener('change', function () {
            section.querySelectorAll('.perm-cb').forEach(cb => { cb.checked = this.checked; });
            refreshSection(section);
        });
        section.querySelectorAll('.perm-cb').forEach(cb => {
            cb.addEventListener('change', () => refreshSection(section));
        });
    });

    document.getElementById('permSelectAll')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-cb').forEach(cb => { cb.checked = true; });
        document.querySelectorAll('.perm-section').forEach(refreshSection);
    });
    document.getElementById('permClearAll')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-cb').forEach(cb => { cb.checked = false; });
        document.querySelectorAll('.perm-section').forEach(refreshSection);
    });
    document.getElementById('permForm')?.addEventListener('submit', function (e) {
        if (this.querySelectorAll('.perm-cb:checked').length === 0
            && !window.confirm('This will remove every assignable permission from this role. Continue?')) {
            e.preventDefault();
        }
    });
})();
JS; ?>
