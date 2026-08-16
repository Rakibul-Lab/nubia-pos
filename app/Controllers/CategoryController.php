<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Category;

/**
 * Category CRUD (modal-driven).
 *
 * @package App\Controllers
 */
final class CategoryController extends Controller
{
    private Category $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Category();
    }

    public function index(): void
    {
        $this->authorize('categories.view');
        $this->view('catalog.categories', [
            'title'      => 'Categories',
            'categories' => $this->model->withCounts(),
            'parents'    => $this->model->all([], 'name'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('categories.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:2|max:120']);
        $this->model->create([
            'name'      => trim((string) $data['name']),
            'slug'      => slugify((string) $data['name']) . '-' . substr(uniqid(), -4),
            'parent_id' => $this->request->int('parent_id') ?: null,
            'status'    => $this->request->bool('status') ? 1 : 1,
        ]);
        ActivityLog::record('category.created', 'Inventory', 'Created category ' . $data['name']);
        flash('success', 'Category created.');
        redirect('categories');
    }

    public function update(string $id): void
    {
        $this->authorize('categories.edit');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:2|max:120']);
        $this->model->update((int) $id, [
            'name'      => trim((string) $data['name']),
            'parent_id' => $this->request->int('parent_id') ?: null,
        ]);
        flash('success', 'Category updated.');
        redirect('categories');
    }

    public function destroy(string $id): void
    {
        $this->authorize('categories.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Category deleted.');
        redirect('categories');
    }
}
