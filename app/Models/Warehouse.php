<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Warehouse / store location model.
 *
 * @package App\Models
 */
final class Warehouse extends Model
{
    protected string $table = 'warehouses';

    protected array $fillable = ['name', 'code', 'phone', 'email', 'address', 'is_default', 'status'];

    public function defaultId(): int
    {
        $id = $this->db->scalar('SELECT id FROM warehouses WHERE is_default = 1 ORDER BY id LIMIT 1');
        return (int) ($id ?: $this->db->scalar('SELECT id FROM warehouses ORDER BY id LIMIT 1') ?: 1);
    }
}
