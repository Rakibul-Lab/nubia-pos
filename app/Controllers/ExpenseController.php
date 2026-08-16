<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Expense;

/**
 * Expense management + categories.
 *
 * @package App\Controllers
 */
final class ExpenseController extends Controller
{
    private Expense $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Expense();
    }

    public function index(): void
    {
        $this->authorize('expenses.view');
        $keyword    = $this->request->string('q');
        $categoryId = $this->request->int('category') ?: null;
        $result     = $this->model->search($keyword, $categoryId, $this->request->int('page', 1));

        $totals = Database::getInstance()->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN expense_date = CURDATE() THEN amount END),0) AS today,
                COALESCE(SUM(CASE WHEN expense_date >= DATE_FORMAT(CURDATE(),'%Y-%m-01') THEN amount END),0) AS month,
                COALESCE(SUM(CASE WHEN expense_date >= DATE_FORMAT(CURDATE(),'%Y-01-01') THEN amount END),0) AS year
             FROM expenses"
        );

        $this->view('expenses.index', [
            'title'      => 'Expenses',
            'expenses'   => $result['data'],
            'meta'       => $result,
            'keyword'    => $keyword,
            'categories' => Database::getInstance()->fetchAll('SELECT * FROM expense_categories ORDER BY name'),
            'totals'     => $totals,
        ]);
    }

    public function store(): void
    {
        $this->authorize('expenses.create');
        $this->verifyCsrf();
        $data = $this->validate([
            'title'  => 'required|max:160',
            'amount' => 'required|numeric',
        ]);
        $this->model->create([
            'reference'    => generate_code('EXP'),
            'category_id'  => $this->request->int('category_id') ?: null,
            'title'        => trim((string) $data['title']),
            'amount'       => $this->request->float('amount'),
            'method'       => $this->request->string('method', 'cash'),
            'expense_date' => $this->request->string('expense_date', date('Y-m-d')),
            'note'         => $this->request->string('note') ?: null,
            'created_by'   => Auth::id(),
        ]);
        flash('success', 'Expense recorded.');
        redirect('expenses');
    }

    public function update(string $id): void
    {
        $this->authorize('expenses.edit');
        $this->verifyCsrf();
        $data = $this->validate([
            'title'  => 'required|max:160',
            'amount' => 'required|numeric',
        ]);
        $this->model->update((int) $id, [
            'category_id'  => $this->request->int('category_id') ?: null,
            'title'        => trim((string) $data['title']),
            'amount'       => $this->request->float('amount'),
            'method'       => $this->request->string('method', 'cash'),
            'expense_date' => $this->request->string('expense_date', date('Y-m-d')),
        ]);
        flash('success', 'Expense updated.');
        redirect('expenses');
    }

    public function destroy(string $id): void
    {
        $this->authorize('expenses.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Expense deleted.');
        redirect('expenses');
    }

    public function categories(): void
    {
        $this->authorize('expense_categories.view');
        $this->view('expenses.categories', [
            'title'      => 'Expense Categories',
            'categories' => Database::getInstance()->fetchAll('SELECT ec.*, (SELECT COUNT(*) FROM expenses WHERE category_id = ec.id) AS cnt FROM expense_categories ec ORDER BY name'),
        ]);
    }

    public function storeCategory(): void
    {
        $this->authorize('expense_categories.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|max:120']);
        Database::getInstance()->insert('expense_categories', ['name' => trim((string) $data['name']), 'status' => 1]);
        flash('success', 'Category added.');
        redirect('expense-categories');
    }
}
