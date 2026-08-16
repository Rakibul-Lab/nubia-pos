<?php
use App\Core\View;
/** @var array $expenses @var array $meta @var string $keyword @var array $categories @var array $totals */
$expenseActions = '';
if (can('expense_categories.view')) {
    $expenseActions .= '<a href="' . url('expense-categories') . '" class="btn btn-soft"><span class="material-symbols-rounded">category</span> Categories</a>';
}
if (can('expenses.create')) {
    $expenseActions .= ' <button class="btn btn-brand" onclick="expModal.show()"><span class="material-symbols-rounded">add</span> Add Expense</button>';
}
?>
<?= View::partial('components.page_head', [
    'title'    => 'Expenses',
    'subtitle' => 'Track daily, monthly & yearly business expenses',
    'extraActions' => $expenseActions,
]) ?>

<div class="row g-3 mb-1">
    <div class="col-md-4"><div class="stat-card"><div class="stat-icon bg-grad-3"><span class="material-symbols-rounded">today</span></div><div class="stat-label">Today</div><div class="stat-value"><?= money($totals['today']) ?></div></div></div>
    <div class="col-md-4"><div class="stat-card"><div class="stat-icon bg-grad-5"><span class="material-symbols-rounded">calendar_month</span></div><div class="stat-label">This Month</div><div class="stat-value"><?= money($totals['month']) ?></div></div></div>
    <div class="col-md-4"><div class="stat-card"><div class="stat-icon bg-grad-1"><span class="material-symbols-rounded">event</span></div><div class="stat-label">This Year</div><div class="stat-value"><?= money($totals['year']) ?></div></div></div>
</div>

<div class="card mt-3">
    <div class="table-wrap">
        <table class="nubia">
            <thead><tr><th>Reference</th><th>Title</th><th>Category</th><th>Date</th><th>Method</th><th>Amount</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$expenses): ?><tr><td colspan="7"><?= View::partial('components.empty', ['icon' => 'payments', 'title' => 'No expenses']) ?></td></tr><?php endif; ?>
            <?php foreach ($expenses as $ex): ?>
                <tr>
                    <td class="fw-800"><?= e($ex['reference']) ?></td>
                    <td><?= e($ex['title']) ?></td>
                    <td><span class="badge-pill badge-muted"><?= e($ex['category_name'] ?? 'Uncategorized') ?></span></td>
                    <td><?= date('M j, Y', strtotime($ex['expense_date'])) ?></td>
                    <td><?= ucfirst($ex['method']) ?></td>
                    <td class="fw-800 text-danger"><?= money($ex['amount']) ?></td>
                    <td class="text-end">
                        <?php if (can('expenses.edit')): ?><button class="icon-btn" style="width:34px;height:34px;display:inline-grid;" onclick='editExp(<?= json_encode(["id" => $ex["id"], "title" => $ex["title"], "amount" => $ex["amount"], "category_id" => $ex["category_id"], "method" => $ex["method"], "expense_date" => $ex["expense_date"]]) ?>)'><span class="material-symbols-rounded" style="font-size:18px;">edit</span></button><?php endif; ?>
                        <?php if (can('expenses.delete')): ?><form method="POST" action="<?= url('expenses/' . $ex['id']) ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="button" class="icon-btn" style="width:34px;height:34px;" data-confirm-delete="Delete expense?"><span class="material-symbols-rounded" style="font-size:18px;color:var(--danger);">delete</span></button></form><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= View::partial('components.pagination', ['meta' => $meta, 'baseUrl' => url('expenses') . '?q=' . urlencode($keyword)]) ?></div>
</div>

<?php if (can('expenses.create') || can('expenses.edit')): ?><div class="modal fade" id="expModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="POST" id="expForm" action="<?= url('expenses') ?>"><?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title" id="expTitle">Add Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Title *</label><input type="text" name="title" id="expTitleInput" class="form-control" required></div>
            <div class="row g-2">
                <div class="col-6 mb-2"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" id="expAmount" class="form-control" required></div>
                <div class="col-6 mb-2"><label class="form-label">Date</label><input type="date" name="expense_date" id="expDate" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-6 mb-2"><label class="form-label">Category</label><select name="category_id" id="expCat" class="form-select"><option value="">— None —</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-6 mb-2"><label class="form-label">Method</label><select name="method" id="expMethod" class="form-select"><?php foreach (payment_methods(false) as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div><?php endif; ?>

<?php if (can('expenses.create') || can('expenses.edit')) $pageScript = <<<'JS'
const expModal = new bootstrap.Modal('#expModal');
function editExp(x){ document.getElementById('expForm').action = NUBIA_BASE + '/expenses/' + x.id; document.getElementById('expTitle').textContent='Edit Expense';
    document.getElementById('expTitleInput').value=x.title; document.getElementById('expAmount').value=x.amount; document.getElementById('expCat').value=x.category_id||''; document.getElementById('expMethod').value=x.method; document.getElementById('expDate').value=x.expense_date; expModal.show(); }
document.querySelector('[onclick="expModal.show()"]')?.addEventListener('click',()=>{ document.getElementById('expForm').action = NUBIA_BASE + '/expenses'; document.getElementById('expTitle').textContent='Add Expense'; document.getElementById('expForm').reset(); });
JS; ?>
