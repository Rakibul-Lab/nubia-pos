<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Expense model.
 *
 * @package App\Models
 */
final class Expense extends Model
{
    protected string $table = 'expenses';

    protected array $fillable = [
        'reference', 'category_id', 'warehouse_id', 'title', 'amount', 'method', 'expense_date', 'note', 'created_by',
    ];

    public function search(string $keyword = '', ?int $categoryId = null, int $page = 1, int $perPage = 15): array
    {
        $sql = 'SELECT e.*, ec.name AS category_name
                FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id
                WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (e.title LIKE ? OR e.reference LIKE ?)';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like);
        }
        if ($categoryId) {
            $sql .= ' AND e.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY e.expense_date DESC, e.id DESC';
        return $this->paginate($sql, $params, $page, $perPage);
    }
}
