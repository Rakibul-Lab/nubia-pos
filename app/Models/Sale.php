<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Sale (invoice) model.
 *
 * @package App\Models
 */
final class Sale extends Model
{
    protected string $table = 'sales';

    public function search(string $keyword = '', string $status = '', int $page = 1, int $perPage = 15): array
    {
        $sql = 'SELECT s.*, COALESCE(c.name, "Walk-in Customer") AS customer_name, u.name AS cashier
                FROM sales s
                LEFT JOIN customers c ON c.id = s.customer_id
                LEFT JOIN users u ON u.id = s.created_by
                WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (s.invoice_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like, $like);
        }
        if ($status !== '') {
            $sql .= ' AND s.payment_status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY s.id DESC';
        return $this->paginate($sql, $params, $page, $perPage);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT s.*, c.name AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
                    u.name AS cashier, w.name AS warehouse_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.created_by
             LEFT JOIN warehouses w ON w.id = s.warehouse_id
             WHERE s.id = ? LIMIT 1',
            [$id]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function items(int $saleId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT si.*, p.name AS product_name, p.sku, p.has_imei, p.has_serial,
                    (si.quantity - si.returned_qty) AS returnable_qty
             FROM sale_items si JOIN products p ON p.id = si.product_id
             WHERE si.sale_id = ?',
            [$saleId]
        );
        foreach ($rows as &$row) {
            $imeis = $this->db->fetchAll(
                'SELECT COALESCE(imei, serial_no) AS code FROM product_serials WHERE sale_item_id = ? ORDER BY id',
                [(int) $row['id']]
            );
            $row['imeis'] = array_values(array_filter(array_map(
                static fn (array $r): string => (string) ($r['code'] ?? ''),
                $imeis
            )));
        }
        unset($row);
        return $rows;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function exchangesForSale(int $saleId): array
    {
        return $this->db->fetchAll(
            'SELECT e.*,
                    os.invoice_no AS original_invoice_no,
                    ns.invoice_no AS new_invoice_no
             FROM sale_exchanges e
             LEFT JOIN sales os ON os.id = e.original_sale_id
             LEFT JOIN sales ns ON ns.id = e.new_sale_id
             WHERE e.original_sale_id = ? OR e.new_sale_id = ?
             ORDER BY e.id DESC',
            [$saleId, $saleId]
        );
    }

    /**
     * Exchange context for a sale used on invoices / show page.
     *
     * @return array{is_exchange:bool,exchange_ref:?string,original_invoice:?string,original_sale_id:?int,as_original:bool,count:int}|null
     */
    public function exchangeContext(int $saleId): ?array
    {
        $asNew = $this->db->fetch(
            'SELECT e.reference, e.original_sale_id, s.invoice_no AS original_invoice
             FROM sale_exchanges e
             JOIN sales s ON s.id = e.original_sale_id
             WHERE e.new_sale_id = ?
             ORDER BY e.id DESC LIMIT 1',
            [$saleId]
        );
        if ($asNew) {
            return [
                'is_exchange'       => true,
                'exchange_ref'      => $asNew['reference'],
                'original_invoice'  => $asNew['original_invoice'],
                'original_sale_id'  => (int) $asNew['original_sale_id'],
                'as_original'       => false,
                'count'             => 1,
            ];
        }

        $count = (int) $this->db->scalar(
            'SELECT COUNT(*) FROM sale_exchanges WHERE original_sale_id = ?',
            [$saleId]
        );
        if ($count > 0) {
            return [
                'is_exchange'       => false,
                'exchange_ref'      => null,
                'original_invoice'  => null,
                'original_sale_id'  => null,
                'as_original'       => true,
                'count'             => $count,
            ];
        }

        return null;
    }
}
