<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Warehouse;

/**
 * Warehouse CRUD (modal-driven).
 *
 * @package App\Controllers
 */
final class WarehouseController extends Controller
{
    private Warehouse $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Warehouse();
    }

    public function index(): void
    {
        $this->authorize('warehouses.view');
        $this->view('catalog.warehouses', [
            'title'      => 'Warehouses',
            'warehouses' => $this->model->all([], 'name'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('warehouses.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|max:120']);
        $this->model->create([
            'name'       => trim((string) $data['name']),
            'code'       => $this->request->string('code') ?: 'WH-' . strtoupper(substr(uniqid(), -5)),
            'phone'      => $this->request->string('phone') ?: null,
            'address'    => $this->request->string('address') ?: null,
            'is_default' => $this->request->bool('is_default') ? 1 : 0,
            'status'     => 1,
        ]);
        flash('success', 'Warehouse created.');
        redirect('warehouses');
    }

    public function update(string $id): void
    {
        $this->authorize('warehouses.edit');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|max:120']);
        $this->model->update((int) $id, [
            'name'    => trim((string) $data['name']),
            'phone'   => $this->request->string('phone') ?: null,
            'address' => $this->request->string('address') ?: null,
        ]);
        flash('success', 'Warehouse updated.');
        redirect('warehouses');
    }

    public function destroy(string $id): void
    {
        $this->authorize('warehouses.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Warehouse deleted.');
        redirect('warehouses');
    }
}
