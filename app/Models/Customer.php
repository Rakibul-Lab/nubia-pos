<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Customer model with ledger & balance helpers.
 *
 * @package App\Models
 */
final class Customer extends Model
{
    protected string $table = 'customers';

    protected array $fillable = [
        'name', 'phone', 'email', 'company', 'address', 'city', 'type',
        'opening_balance', 'credit_limit', 'loyalty_points', 'status',
    ];

    public function search(string $keyword = '', int $page = 1, int $perPage = 15): array
    {
        $sql = 'SELECT c.*,
                       COALESCE((SELECT SUM(due) FROM sales WHERE customer_id = c.id AND status != "cancelled"),0) AS due_amount,
                       COALESCE((SELECT SUM(total) FROM sales WHERE customer_id = c.id AND status != "cancelled"),0) AS total_purchased
                FROM customers c WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like, $like);
        }
        $sql .= ' ORDER BY c.id DESC';
        return $this->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Total outstanding due for a customer (opening balance + sales due − payments).
     */
    public function balance(int $customerId): float
    {
        $customer = $this->find($customerId);
        $salesDue = (float) $this->db->scalar(
            'SELECT COALESCE(SUM(due),0) FROM sales WHERE customer_id = ? AND status != "cancelled"',
            [$customerId]
        );
        return (float) ($customer['opening_balance'] ?? 0) + $salesDue;
    }

    /**
     * Build a chronological ledger of sales & payments.
     *
     * @return array<int,array<string,mixed>>
     */
    public function ledger(int $customerId): array
    {
        $sales = $this->db->fetchAll(
            'SELECT sale_date AS date, invoice_no AS ref, total AS debit, 0 AS credit, "Sale" AS type
             FROM sales WHERE customer_id = ? AND status != "cancelled"',
            [$customerId]
        );
        $payments = $this->db->fetchAll(
            'SELECT paid_at AS date, reference AS ref, 0 AS debit, amount AS credit, "Payment" AS type
             FROM payments WHERE party_type = "customer" AND party_id = ?',
            [$customerId]
        );
        $rows = array_merge($sales, $payments);
        usort($rows, static fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));
        return $rows;
    }
}
