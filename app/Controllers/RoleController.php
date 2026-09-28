<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\PermissionRegistry;
use App\Core\Response;
use App\Models\Role;

/**
 * Role & permission management (RBAC).
 *
 * @package App\Controllers
 */
final class RoleController extends Controller
{
    private Role $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Role();
    }

    public function index(): void
    {
        $this->authorize('roles.view');
        $this->view('roles.index', ['title' => 'Roles', 'roles' => $this->model->withCounts()]);
    }

    public function store(): void
    {
        $this->authorize('roles.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|max:80']);
        $this->model->create([
            'name'        => trim((string) $data['name']),
            'slug'        => slugify((string) $data['name']),
            'description' => $this->request->string('description') ?: null,
            'is_system'   => 0,
        ]);
        flash('success', 'Role created.');
        redirect('roles');
    }

    public function permissions(string $id): void
    {
        $this->authorize('roles.permissions');
        $role = $this->model->find((int) $id);
        if (!$role) {
            Response::abort(404);
        }
        $permissions = Database::getInstance()->fetchAll(
            Auth::hasRole('super-admin')
                ? 'SELECT * FROM permissions ORDER BY module, name'
                : 'SELECT p.*
                   FROM permissions p
                   JOIN role_permissions rp ON rp.permission_id = p.id
                   WHERE rp.role_id = ?
                   ORDER BY p.module, p.name',
            Auth::hasRole('super-admin') ? [] : [(int) (Auth::user()['role_id'] ?? 0)]
        );
        $grouped = [];
        foreach ($permissions as $p) {
            $grouped[$p['module']][] = $p;
        }

        $catalogOrder = [];
        foreach (PermissionRegistry::catalog() as $module => $items) {
            $catalogOrder[$module] = array_keys($items);
        }
        foreach ($grouped as $module => $perms) {
            $order = $catalogOrder[$module] ?? [];
            usort($perms, static function (array $a, array $b) use ($order): int {
                $ia = array_search($a['slug'], $order, true);
                $ib = array_search($b['slug'], $order, true);
                $ia = $ia === false ? PHP_INT_MAX : $ia;
                $ib = $ib === false ? PHP_INT_MAX : $ib;
                return $ia <=> $ib;
            });
            $grouped[$module] = $perms;
        }

        // Match sidebar navigation sections.
        $sections = [
            'Main'           => ['icon' => 'grid_view', 'hint' => 'Dashboard KPIs, POS and Direct Buy & Sell'],
            'Inventory'      => ['icon' => 'inventory_2', 'hint' => 'Products, catalog and stock'],
            'Transactions'   => ['icon' => 'receipt_long', 'hint' => 'Sales, purchases and quotations'],
            'People'         => ['icon' => 'groups', 'hint' => 'Customers and suppliers'],
            'Finance'        => ['icon' => 'account_balance_wallet', 'hint' => 'Expenses, accounting and reports'],
            'Administration' => ['icon' => 'admin_panel_settings', 'hint' => 'Users, roles, logs and settings'],
        ];

        $sorted = [];
        foreach ($sections as $module => $meta) {
            if (!isset($grouped[$module])) {
                continue;
            }
            $sorted[$module] = [
                'icon'  => $meta['icon'],
                'hint'  => $meta['hint'],
                'items' => $grouped[$module],
            ];
            unset($grouped[$module]);
        }
        foreach ($grouped as $module => $perms) {
            $sorted[$module] = [
                'icon'  => 'folder',
                'hint'  => '',
                'items' => $perms,
            ];
        }

        $this->view('roles.permissions', [
            'title'    => 'Permissions · ' . $role['name'],
            'role'     => $role,
            'grouped'  => $sorted,
            'assigned' => ($role['slug'] ?? '') === 'super-admin'
                ? array_map('intval', array_column($permissions, 'id'))
                : $this->model->permissionIds((int) $id),
            'immutable' => ($role['slug'] ?? '') === 'super-admin',
        ]);
    }

    public function savePermissions(string $id): void
    {
        $this->authorize('roles.permissions');
        $this->verifyCsrf();
        $role = $this->model->find((int) $id);
        if (!$role) {
            Response::abort(404);
        }
        if (($role['slug'] ?? '') === 'super-admin') {
            flash('error', 'Super Admin always has unrestricted access.');
            redirect('roles');
        }
        $ids = array_values(array_unique(array_map('intval', (array) $this->request->input('permissions', []))));
        if (!Auth::hasRole('super-admin')) {
            $allowedRows = Database::getInstance()->fetchAll(
                'SELECT permission_id FROM role_permissions WHERE role_id = ?',
                [(int) (Auth::user()['role_id'] ?? 0)]
            );
            $allowed = array_map('intval', array_column($allowedRows, 'permission_id'));
            $ids = array_values(array_intersect($ids, $allowed));

            // A delegated role manager cannot grant or revoke capabilities
            // they do not possess themselves.
            $existing = $this->model->permissionIds((int) $id);
            $hidden = array_diff($existing, $allowed);
            $ids = array_values(array_unique(array_merge($ids, $hidden)));

            if ((int) $id === (int) (Auth::user()['role_id'] ?? 0)) {
                $selfGuard = Database::getInstance()->fetchAll(
                    'SELECT id FROM permissions WHERE slug IN ("roles.view", "roles.permissions")'
                );
                $ids = array_values(array_unique(array_merge(
                    $ids,
                    array_map('intval', array_column($selfGuard, 'id'))
                )));
            }
        }
        $this->model->syncPermissions((int) $id, $ids);
        flash('success', 'Permissions updated.');
        redirect('roles');
    }

    public function destroy(string $id): void
    {
        $this->authorize('roles.delete');
        $this->verifyCsrf();
        $role = $this->model->find((int) $id);
        if ($role && (int) $role['is_system'] === 1) {
            flash('error', 'System roles cannot be deleted.');
            redirect('roles');
        }
        $this->model->delete((int) $id);
        flash('success', 'Role deleted.');
        redirect('roles');
    }
}
