<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Role model with permission assignment helpers.
 *
 * @package App\Models
 */
final class Role extends Model
{
    protected string $table = 'roles';

    protected array $fillable = ['name', 'slug', 'description', 'is_system'];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function withCounts(): array
    {
        return $this->db->fetchAll(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM users WHERE role_id = r.id) AS user_count,
                    (SELECT COUNT(*) FROM role_permissions WHERE role_id = r.id) AS permission_count
             FROM roles r ORDER BY r.id'
        );
    }

    /**
     * @return int[]
     */
    public function permissionIds(int $roleId): array
    {
        $rows = $this->db->fetchAll('SELECT permission_id FROM role_permissions WHERE role_id = ?', [$roleId]);
        return array_map('intval', array_column($rows, 'permission_id'));
    }

    /**
     * @param int[] $permissionIds
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->transaction(function () use ($roleId, $permissionIds): void {
            $this->db->query('DELETE FROM role_permissions WHERE role_id = ?', [$roleId]);
            foreach ($permissionIds as $pid) {
                $this->db->insert('role_permissions', ['role_id' => $roleId, 'permission_id' => (int) $pid]);
            }
        });
    }
}
