<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Direct Buy & Sell transaction model (special feature).
 *
 * @package App\Models
 */
final class DirectTransaction extends Model
{
    protected string $table = 'direct_transactions';

    public function recent(int $page = 1, int $perPage = 15): array
    {
        $sql = 'SELECT d.*, u.name AS created_name
                FROM direct_transactions d
                LEFT JOIN users u ON u.id = d.created_by
                ORDER BY d.id DESC';
        return $this->paginate($sql, [], $page, $perPage);
    }
}
