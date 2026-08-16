<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Product model with catalog joins and stock awareness.
 *
 * @package App\Models
 */
final class Product extends Model
{
    protected string $table = 'products';

    protected array $fillable = [
        'category_id', 'brand_id', 'unit_id', 'name', 'slug', 'sku', 'barcode', 'type',
        'description', 'image', 'cost_price', 'selling_price', 'wholesale_price', 'tax_rate',
        'alert_quantity', 'has_serial', 'has_imei', 'has_expiry', 'status', 'created_by',
    ];

    /**
     * Paginated product listing with search + category filter.
     *
     * @return array{data:array,total:int,page:int,per_page:int,last_page:int}
     */
    public function search(
        string $keyword = '',
        ?int $categoryId = null,
        int $page = 1,
        int $perPage = 12,
        ?int $warehouseId = null
    ): array {
        $stockExpr = $warehouseId
            ? 'COALESCE((SELECT SUM(quantity) FROM stock WHERE product_id = p.id AND warehouse_id = ' . (int) $warehouseId . '), 0)'
            : 'COALESCE((SELECT SUM(quantity) FROM stock WHERE product_id = p.id), 0)';

        $sql = 'SELECT p.*, c.name AS category_name, b.name AS brand_name, u.short_name AS unit,
                       ' . $stockExpr . ' AS stock
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN brands b ON b.id = p.brand_id
                LEFT JOIN units u ON u.id = p.unit_id
                WHERE 1=1';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' AND (
                p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?
                OR EXISTS (
                    SELECT 1 FROM product_serials ps
                    WHERE ps.product_id = p.id
                      AND (ps.imei LIKE ? OR ps.serial_no LIKE ?)
                )
            )';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY p.id DESC';

        return $this->paginate($sql, $params, $page, $perPage);
    }

    /**
     * All matching products for export (no pagination).
     *
     * @return array<int,array<string,mixed>>
     */
    public function exportRows(string $keyword = '', ?int $categoryId = null): array
    {
        $sql = 'SELECT p.name, p.sku, p.barcode, c.name AS category_name, b.name AS brand_name,
                       u.short_name AS unit, p.cost_price, p.selling_price, p.wholesale_price,
                       p.alert_quantity, p.status,
                       COALESCE((SELECT SUM(quantity) FROM stock WHERE product_id = p.id), 0) AS stock
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN brands b ON b.id = p.brand_id
                LEFT JOIN units u ON u.id = p.unit_id
                WHERE 1=1';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' AND (
                p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?
                OR EXISTS (
                    SELECT 1 FROM product_serials ps
                    WHERE ps.product_id = p.id
                      AND (ps.imei LIKE ? OR ps.serial_no LIKE ?)
                )
            )';
            $like = '%' . $keyword . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY p.name ASC';

        $rows = $this->db->fetchAll($sql, $params);
        foreach ($rows as &$row) {
            $row['status'] = ((int) ($row['status'] ?? 0) === 1) ? 'Active' : 'Inactive';
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT p.*, c.name AS category_name, b.name AS brand_name, u.name AS unit_name, u.short_name AS unit,
                    COALESCE((SELECT SUM(quantity) FROM stock WHERE product_id = p.id), 0) AS stock
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             LEFT JOIN units u ON u.id = p.unit_id
             WHERE p.id = ? LIMIT 1',
            [$id]
        );
    }

    /**
     * Lightweight search for POS / autocomplete.
     *
     * @return array<int,array<string,mixed>>
     */
    public function quickSearch(
        string $keyword,
        int $warehouseId = 0,
        int $limit = 20,
        bool $inStockOnly = false
    ): array {
        $keyword = trim($keyword);
        $stockJoin = $warehouseId > 0
            ? 'COALESCE((SELECT SUM(quantity) FROM stock WHERE product_id = p.id AND warehouse_id = ' . (int) $warehouseId . '), 0)'
            : 'COALESCE((SELECT SUM(quantity) FROM stock WHERE product_id = p.id), 0)';

        $select = "SELECT p.id, p.name, p.sku, p.barcode, p.selling_price, p.wholesale_price, p.cost_price, p.tax_rate,
                    p.has_imei, p.has_serial,
                    {$stockJoin} AS stock
             FROM products p
             WHERE p.status = 1";

        // Scans (exact barcode / SKU / IMEI) stay unfiltered so the UI can explain
        // that the unit exists but is not stocked in the selected warehouse.
        $stockFilter = $inStockOnly ? " AND {$stockJoin} > 0" : '';

        if ($keyword === '') {
            return $this->db->fetchAll(
                $select . $stockFilter . ' ORDER BY p.name LIMIT ' . (int) $limit
            );
        }

        // Exact IMEI / serial → single available unit (POS scan).
        $serial = $this->db->fetch(
            'SELECT ps.imei, ps.serial_no, ps.warehouse_id, p.id, p.name, p.sku, p.barcode,
                    p.selling_price, p.wholesale_price, p.cost_price, p.tax_rate, p.has_imei, p.has_serial,
                    1 AS stock
             FROM product_serials ps
             JOIN products p ON p.id = ps.product_id
             WHERE ps.status = \'available\' AND (ps.imei = ? OR ps.serial_no = ?) AND p.status = 1
             LIMIT 1',
            [$keyword, $keyword]
        );
        if ($serial) {
            if ($warehouseId > 0 && (int) ($serial['warehouse_id'] ?? 0) !== $warehouseId) {
                // Still return product but mark out of stock for this warehouse.
                $serial['stock'] = 0;
            }
            $serial['matched_imei'] = $serial['imei'] ?: $serial['serial_no'];
            return [$serial];
        }

        // Exact barcode / SKU first (POS scanners + camera).
        $exact = $this->db->fetchAll(
            $select . ' AND (p.barcode = ? OR p.sku = ?) ORDER BY p.name LIMIT ' . (int) $limit,
            [$keyword, $keyword]
        );
        if ($exact !== []) {
            return $exact;
        }

        $like = '%' . $keyword . '%';
        return $this->db->fetchAll(
            $select . $stockFilter . ' AND (
                p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?
                OR EXISTS (
                    SELECT 1 FROM product_serials ps
                    WHERE ps.product_id = p.id
                      AND (ps.imei LIKE ? OR ps.serial_no LIKE ?)
                )
             )
             ORDER BY
                CASE
                    WHEN p.barcode = ? OR p.sku = ? THEN 0
                    WHEN EXISTS (
                        SELECT 1 FROM product_serials ps2
                        WHERE ps2.product_id = p.id AND (ps2.imei = ? OR ps2.serial_no = ?)
                    ) THEN 0
                    WHEN p.barcode LIKE ? OR p.sku LIKE ? THEN 1
                    WHEN EXISTS (
                        SELECT 1 FROM product_serials ps3
                        WHERE ps3.product_id = p.id AND (ps3.imei LIKE ? OR ps3.serial_no LIKE ?)
                    ) THEN 1
                    ELSE 2
                END,
                p.name
             LIMIT ' . (int) $limit,
            [
                $like, $like, $like, $like, $like,
                $keyword, $keyword,
                $keyword, $keyword,
                $keyword . '%', $keyword . '%',
                $keyword . '%', $keyword . '%',
            ]
        );
    }
}
