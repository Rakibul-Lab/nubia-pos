<?php
use App\Core\View;
/** @var array $categories */
?>
<?= View::partial('components.page_head', [
    'title'    => 'Expense Categories',
    'subtitle' => 'Organize expenses',
    'extraActions' => can('expenses.view') ? '<a href="' . url('expenses') . '" class="btn btn-soft">Back to Expenses</a>' : '',
]) ?>
<div class="row g-3">
    <?php if (can('expense_categories.create')): ?><div class="col-lg-4">
        <div class="card"><div class="card-header">Add Category</div><div class="card-body">
            <form method="POST" action="<?= url('expense-categories') ?>"><?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                <button class="btn btn-brand w-100">Add Category</button>
            </form>
        </div></div>
    </div><?php endif; ?>
    <div class="<?= can('expense_categories.create') ? 'col-lg-8' : 'col-12' ?>">
        <div class="card"><div class="table-wrap"><table class="nubia">
            <thead><tr><th>Name</th><th>Expenses</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr><td class="fw-800"><?= e($c['name']) ?></td><td><span class="badge-pill badge-info"><?= (int) $c['cnt'] ?></span></td><td><span class="badge-pill <?= $c['status'] ? 'badge-success' : 'badge-muted' ?>"><?= $c['status'] ? 'Active' : 'Inactive' ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
