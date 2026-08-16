<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Unit;

/**
 * Unit CRUD (modal-driven).
 *
 * @package App\Controllers
 */
final class UnitController extends Controller
{
    private Unit $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Unit();
    }

    public function index(): void
    {
        $this->authorize('units.view');
        $this->view('catalog.units', [
            'title' => 'Units',
            'units' => $this->model->all([], 'name'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('units.create');
        $this->verifyCsrf();
        $data = $this->validate([
            'name'       => 'required|max:80',
            'short_name' => 'required|max:20',
        ]);
        $this->model->create([
            'name'       => trim((string) $data['name']),
            'short_name' => trim((string) $data['short_name']),
            'status'     => 1,
        ]);
        flash('success', 'Unit created.');
        redirect('units');
    }

    public function update(string $id): void
    {
        $this->authorize('units.edit');
        $this->verifyCsrf();
        $data = $this->validate([
            'name'       => 'required|max:80',
            'short_name' => 'required|max:20',
        ]);
        $this->model->update((int) $id, [
            'name'       => trim((string) $data['name']),
            'short_name' => trim((string) $data['short_name']),
        ]);
        flash('success', 'Unit updated.');
        redirect('units');
    }

    public function destroy(string $id): void
    {
        $this->authorize('units.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Unit deleted.');
        redirect('units');
    }
}
