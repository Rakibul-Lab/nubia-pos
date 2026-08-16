<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;

/**
 * User management.
 *
 * @package App\Controllers
 */
final class UserController extends Controller
{
    private User $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new User();
    }

    public function index(): void
    {
        $this->authorize('users.view');
        $this->view('users.index', ['title' => 'Users', 'users' => $this->model->withRoles()]);
    }

    public function create(): void
    {
        $this->authorize('users.create');
        $this->view('users.form', $this->formData(null));
    }

    public function edit(string $id): void
    {
        $this->authorize('users.edit');
        $user = $this->model->find((int) $id);
        if (!$user) {
            Response::abort(404);
        }
        $this->assertRoleAssignable((int) $user['role_id']);
        $this->view('users.form', $this->formData($user));
    }

    public function store(): void
    {
        $this->authorize('users.create');
        $this->verifyCsrf();
        $rules = [
            'name'     => 'required|min:2|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ];
        if (Auth::can('users.assign_roles')) {
            $rules['role_id'] = 'required|integer';
        }
        $data = $this->validate($rules);
        $roleId = Auth::can('users.assign_roles')
            ? (int) $data['role_id']
            : (int) Database::getInstance()->scalar('SELECT id FROM roles WHERE slug = "employee" LIMIT 1');
        $this->assertRoleAssignable($roleId);
        $id = $this->model->create([
            'role_id'      => $roleId,
            'warehouse_id' => Auth::can('users.assign_warehouses')
                ? ($this->request->int('warehouse_id') ?: null)
                : (Auth::user()['warehouse_id'] ?? null),
            'name'         => trim((string) $data['name']),
            'email'        => strtolower(trim((string) $data['email'])),
            'phone'        => $this->request->string('phone') ?: null,
            'password'     => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'status'       => $this->request->string('status', 'active'),
        ]);
        ActivityLog::record('user.created', 'Users', 'Created user ' . $data['email'], 'user', (int) $id);
        flash('success', 'User created.');
        redirect('users');
    }

    public function update(string $id): void
    {
        $this->authorize('users.edit');
        $this->verifyCsrf();
        $user = $this->model->find((int) $id);
        if (!$user) {
            Response::abort(404);
        }
        $this->assertRoleAssignable((int) $user['role_id']);

        $rules = [
            'name'    => 'required|min:2|max:120',
            'email'   => 'required|email|unique:users,email,' . $id,
        ];
        if (Auth::can('users.assign_roles')) {
            $rules['role_id'] = 'required|integer';
        }
        $data = $this->validate($rules);
        $roleId = Auth::can('users.assign_roles') ? (int) $data['role_id'] : (int) $user['role_id'];
        $this->assertRoleAssignable($roleId);
        $payload = [
            'role_id'      => $roleId,
            'warehouse_id' => Auth::can('users.assign_warehouses')
                ? ($this->request->int('warehouse_id') ?: null)
                : $user['warehouse_id'],
            'name'         => trim((string) $data['name']),
            'email'        => strtolower(trim((string) $data['email'])),
            'phone'        => $this->request->string('phone') ?: null,
            'status'       => $this->request->string('status', 'active'),
        ];
        if ($this->request->string('password') !== '') {
            $payload['password'] = password_hash($this->request->string('password'), PASSWORD_DEFAULT);
        }
        $this->model->update((int) $id, $payload);
        flash('success', 'User updated.');
        redirect('users');
    }

    public function destroy(string $id): void
    {
        $this->authorize('users.delete');
        $this->verifyCsrf();
        if ((int) $id === \App\Core\Auth::id()) {
            flash('error', 'You cannot delete your own account.');
            redirect('users');
        }
        $user = $this->model->find((int) $id);
        if (!$user) {
            Response::abort(404);
        }
        $this->assertRoleAssignable((int) $user['role_id']);
        $this->model->delete((int) $id);
        flash('success', 'User deleted.');
        redirect('users');
    }

    /**
     * @param array<string,mixed>|null $user
     * @return array<string,mixed>
     */
    private function formData(?array $user): array
    {
        $roles = (new Role())->all([], 'id');
        if (!Auth::can('users.assign_super_admin')) {
            $roles = array_values(array_filter(
                $roles,
                static fn (array $role): bool => ($role['slug'] ?? '') !== 'super-admin'
            ));
        }
        return [
            'title'      => $user ? 'Edit User' : 'New User',
            'user'       => $user,
            'roles'      => $roles,
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
            'canAssignRoles' => Auth::can('users.assign_roles'),
            'canAssignWarehouses' => Auth::can('users.assign_warehouses'),
            'currentRoleName' => $user
                ? ((new Role())->find((int) $user['role_id'])['name'] ?? 'Unchanged')
                : 'Employee (default)',
        ];
    }

    private function assertRoleAssignable(int $roleId): void
    {
        $role = (new Role())->find($roleId);
        if (!$role) {
            Response::abort(422, 'Invalid role.');
        }
        if (($role['slug'] ?? '') === 'super-admin' && !Auth::can('users.assign_super_admin')) {
            Response::abort(403);
        }
    }
}
