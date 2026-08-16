<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Purchase model.
 *
 * @package App\Models
 */
final class Purchase extends Model
{
    protected string $table = 'purchases';

    public function search(string $keyword = '', string $status = '', int $page = 1, int $perPage = 15): array
    {
        $sql = 'SELECT p.*, s.name AS supplier_name, w.name AS warehouse_name
                FROM purchases p
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                LEFT JOIN warehouses w ON w.id = p.warehouse_id
                WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (p.reference LIKE ? OR s.name LIKE ?)';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like);
        }
        if ($status !== '') {
            $sql .= ' AND p.payment_status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY p.id DESC';
        return $this->paginate($sql, $params, $page, $perPage);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT p.*, s.name AS supplier_name, s.phone AS supplier_phone, s.address AS supplier_address,
                    w.name AS warehouse_name, u.name AS created_name
             FROM purchases p
             LEFT JOIN suppliers s ON s.id = p.supplier_id
             LEFT JOIN warehouses w ON w.id = p.warehouse_id
             LEFT JOIN users u ON u.id = p.created_by
             WHERE p.id = ? LIMIT 1',
            [$id]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function items(int $purchaseId): array
    {
        return $this->db->fetchAll(
            'SELECT pi.*, p.name AS product_name, p.sku
             FROM purchase_items pi JOIN products p ON p.id = pi.product_id
             WHERE pi.purchase_id = ?',
            [$purchaseId]
        );
    }
}
