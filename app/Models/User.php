<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Application user model.
 *
 * @package App\Models
 */
final class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'role_id', 'warehouse_id', 'name', 'email', 'phone', 'password', 'avatar', 'status',
    ];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function withRoles(): array
    {
        return $this->db->fetchAll(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug FROM users u
             LEFT JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC'
        );
    }
}
