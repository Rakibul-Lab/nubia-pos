<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Per-unit IMEI / serial inventory rows.
 *
 * @package App\Models
 */
final class ProductSerial extends Model
{
    protected string $table = 'product_serials';

    protected array $fillable = [
        'product_id', 'variant_id', 'serial_no', 'imei', 'status',
        'warehouse_id', 'sale_item_id', 'purchase_item_id',
    ];

    /**
     * @return array{data:array<int,array<string,mixed>>,total:int,page:int,per_page:int,last_page:int}
     */
    public function paginateForProduct(int $productId, int $page = 1, int $perPage = 5): array
    {
        $sql = 'SELECT ps.*, w.name AS warehouse_name
                FROM product_serials ps
                LEFT JOIN warehouses w ON w.id = ps.warehouse_id
                WHERE ps.product_id = ?
                ORDER BY ps.status ASC, ps.id DESC';
        return $this->paginate($sql, [$productId], $page, $perPage);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function forProduct(int $productId, ?string $status = null, ?int $warehouseId = null): array
    {
        $sql = 'SELECT ps.*, w.name AS warehouse_name
                FROM product_serials ps
                LEFT JOIN warehouses w ON w.id = ps.warehouse_id
                WHERE ps.product_id = ?';
        $params = [$productId];
        if ($status !== null && $status !== '') {
            $sql .= ' AND ps.status = ?';
            $params[] = $status;
        }
        if ($warehouseId !== null && $warehouseId > 0) {
            $sql .= ' AND ps.warehouse_id = ?';
            $params[] = $warehouseId;
        }
        $sql .= ' ORDER BY ps.status ASC, ps.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findAvailableByImei(string $imei, ?int $warehouseId = null): ?array
    {
        $sql = 'SELECT ps.*, p.name AS product_name, p.sku, p.selling_price, p.cost_price,
                       p.has_imei, p.has_serial, p.tax_rate, p.wholesale_price
                FROM product_serials ps
                JOIN products p ON p.id = ps.product_id
                WHERE ps.status = \'available\' AND (ps.imei = ? OR ps.serial_no = ?)
                AND p.status = 1';
        $params = [$imei, $imei];
        if ($warehouseId !== null && $warehouseId > 0) {
            $sql .= ' AND ps.warehouse_id = ?';
            $params[] = $warehouseId;
        }
        $sql .= ' LIMIT 1';
        return $this->db->fetch($sql, $params);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function availableForProduct(int $productId, int $warehouseId, int $limit = 200): array
    {
        return $this->db->fetchAll(
            'SELECT id, imei, serial_no, warehouse_id
             FROM product_serials
             WHERE product_id = ? AND warehouse_id = ? AND status = \'available\'
             ORDER BY id ASC LIMIT ' . (int) $limit,
            [$productId, $warehouseId]
        );
    }

    /**
     * Available units matching a partial IMEI / serial (POS live search).
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchAvailable(
        int $productId,
        int $warehouseId,
        string $keyword = '',
        int $limit = 20
    ): array {
        $sql = 'SELECT id, imei, serial_no, warehouse_id
                FROM product_serials
                WHERE product_id = ? AND status = \'available\'';
        $params = [$productId];

        if ($warehouseId > 0) {
            $sql .= ' AND warehouse_id = ?';
            $params[] = $warehouseId;
        }

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $sql .= ' AND (imei LIKE ? OR serial_no LIKE ?)';
            $like     = '%' . $keyword . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY id ASC LIMIT ' . max(1, min($limit, 100));

        return $this->db->fetchAll($sql, $params);
    }

    public function availableCount(int $productId, ?int $warehouseId = null): int
    {
        if ($warehouseId !== null && $warehouseId > 0) {
            return (int) $this->db->scalar(
                'SELECT COUNT(*) FROM product_serials WHERE product_id = ? AND warehouse_id = ? AND status = \'available\'',
                [$productId, $warehouseId]
            );
        }
        return (int) $this->db->scalar(
            'SELECT COUNT(*) FROM product_serials WHERE product_id = ? AND status = \'available\'',
            [$productId]
        );
    }

    /**
     * IMEIs attached to a sale item (for invoices).
     *
     * @return array<int,string>
     */
    public function imeisForSaleItem(int $saleItemId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT COALESCE(imei, serial_no) AS code FROM product_serials WHERE sale_item_id = ? ORDER BY id',
            [$saleItemId]
        );
        return array_values(array_filter(array_map(
            static fn (array $r): string => (string) ($r['code'] ?? ''),
            $rows
        )));
    }
}
