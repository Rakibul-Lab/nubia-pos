<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Supplier model with ledger & balance helpers.
 *
 * @package App\Models
 */
final class Supplier extends Model
{
    protected string $table = 'suppliers';

    protected array $fillable = [
        'name', 'phone', 'email', 'company', 'address', 'city', 'opening_balance', 'status',
    ];

    public function search(string $keyword = '', int $page = 1, int $perPage = 15): array
    {
        $sql = 'SELECT s.*,
                       COALESCE((SELECT SUM(due) FROM purchases WHERE supplier_id = s.id AND status != "cancelled"),0) AS due_amount,
                       COALESCE((SELECT SUM(total) FROM purchases WHERE supplier_id = s.id AND status != "cancelled"),0) AS total_purchased
                FROM suppliers s WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (s.name LIKE ? OR s.phone LIKE ? OR s.company LIKE ?)';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like, $like);
        }
        $sql .= ' ORDER BY s.id DESC';
        return $this->paginate($sql, $params, $page, $perPage);
    }

    public function balance(int $supplierId): float
    {
        $supplier   = $this->find($supplierId);
        $purchaseDue = (float) $this->db->scalar(
            'SELECT COALESCE(SUM(due),0) FROM purchases WHERE supplier_id = ? AND status != "cancelled"',
            [$supplierId]
        );
        return (float) ($supplier['opening_balance'] ?? 0) + $purchaseDue;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function ledger(int $supplierId): array
    {
        $purchases = $this->db->fetchAll(
            'SELECT purchase_date AS date, reference AS ref, total AS credit, 0 AS debit, "Purchase" AS type
             FROM purchases WHERE supplier_id = ? AND status != "cancelled"',
            [$supplierId]
        );
        $payments = $this->db->fetchAll(
            'SELECT paid_at AS date, reference AS ref, 0 AS credit, amount AS debit, "Payment" AS type
             FROM payments WHERE party_type = "supplier" AND party_id = ?',
            [$supplierId]
        );
        $rows = array_merge($purchases, $payments);
        usort($rows, static fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));
        return $rows;
    }
}
