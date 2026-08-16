<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Brand;

/**
 * Brand CRUD (modal-driven).
 *
 * @package App\Controllers
 */
final class BrandController extends Controller
{
    private Brand $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Brand();
    }

    public function index(): void
    {
        $this->authorize('brands.view');
        $this->view('catalog.brands', [
            'title'  => 'Brands',
            'brands' => $this->model->all([], 'name'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('brands.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:1|max:120']);
        $this->model->create([
            'name'   => trim((string) $data['name']),
            'slug'   => slugify((string) $data['name']) . '-' . substr(uniqid(), -4),
            'status' => 1,
        ]);
        flash('success', 'Brand created.');
        redirect('brands');
    }

    public function update(string $id): void
    {
        $this->authorize('brands.edit');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:1|max:120']);
        $this->model->update((int) $id, ['name' => trim((string) $data['name'])]);
        flash('success', 'Brand updated.');
        redirect('brands');
    }

    public function destroy(string $id): void
    {
        $this->authorize('brands.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Brand deleted.');
        redirect('brands');
    }
}
