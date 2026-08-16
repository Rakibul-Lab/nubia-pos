<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\ProductSerial;

/**
 * IMEI / serial lifecycle: stock-in, sell, return, transfer, damage.
 *
 * For products with has_imei or has_serial, quantity stock and serial rows
 * stay in sync — every unit in stock is one available serial row.
 *
 * @package App\Services
 */
final class SerialService
{
    /**
     * Parse a free-text list (newline / comma / space separated) into unique codes.
     *
     * @return array<int,string>
     */
    public static function parseList(string|array|null $raw): array
    {
        if (is_array($raw)) {
            $parts = $raw;
        } else {
            $raw = (string) $raw;
            $parts = preg_split('/[\s,;]+/', $raw) ?: [];
        }
        $out = [];
        foreach ($parts as $p) {
            $code = strtoupper(trim((string) $p));
            if ($code === '') {
                continue;
            }
            $out[$code] = $code;
        }
        return array_values($out);
    }

    public static function productTracksUnits(array|int $productOrId): bool
    {
        $db = Database::getInstance();
        if (is_array($productOrId)) {
            return ((int) ($productOrId['has_imei'] ?? 0) === 1)
                || ((int) ($productOrId['has_serial'] ?? 0) === 1);
        }
        $row = $db->fetch('SELECT has_imei, has_serial FROM products WHERE id = ? LIMIT 1', [(int) $productOrId]);
        if (!$row) {
            return false;
        }
        return ((int) $row['has_imei'] === 1) || ((int) $row['has_serial'] === 1);
    }

    public static function prefersImei(array|int $productOrId): bool
    {
        $db = Database::getInstance();
        if (is_array($productOrId)) {
            return (int) ($productOrId['has_imei'] ?? 0) === 1;
        }
        return (int) $db->scalar('SELECT has_imei FROM products WHERE id = ?', [(int) $productOrId]) === 1;
    }

    /**
     * Ensure codes are not already in the system (any status).
     *
     * @param array<int,string> $codes
     */
    public static function assertUnique(array $codes, bool $asImei = true): void
    {
        if ($codes === []) {
            return;
        }
        $db = Database::getInstance();
        $col = $asImei ? 'imei' : 'serial_no';
        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $existing = $db->fetchAll(
            "SELECT {$col} AS code FROM product_serials WHERE {$col} IN ({$placeholders})",
            $codes
        );
        if ($existing !== []) {
            $dupes = implode(', ', array_column($existing, 'code'));
            throw new \InvalidArgumentException('Duplicate IMEI/serial already in system: ' . $dupes);
        }
    }

    /**
     * Stock-in: create available serial rows (does NOT change stock qty — caller does).
     *
     * @param array<int,string> $codes
     * @return array<int,int> inserted serial ids
     */
    public static function stockIn(
        int $productId,
        int $warehouseId,
        array $codes,
        ?int $purchaseItemId = null,
        bool $asImei = true
    ): array {
        $codes = self::parseList($codes);
        if ($codes === []) {
            throw new \InvalidArgumentException('Enter at least one IMEI / serial number.');
        }
        self::assertUnique($codes, $asImei);

        $db = Database::getInstance();
        $ids = [];
        foreach ($codes as $code) {
            $ids[] = $db->insert('product_serials', [
                'product_id'       => $productId,
                'variant_id'       => null,
                'serial_no'        => $asImei ? null : $code,
                'imei'             => $asImei ? $code : null,
                'status'           => 'available',
                'warehouse_id'     => $warehouseId,
                'sale_item_id'     => null,
                'purchase_item_id' => $purchaseItemId,
            ]);
        }
        return $ids;
    }

    /**
     * Mark specific available units as sold and link to sale_item.
     *
     * @param array<int,string> $codes IMEI or serial values
     */
    public static function markSold(
        int $productId,
        int $warehouseId,
        array $codes,
        int $saleItemId
    ): void {
        $codes = self::parseList($codes);
        if ($codes === []) {
            throw new \InvalidArgumentException('IMEI / serial required for this product.');
        }

        $db = Database::getInstance();
        foreach ($codes as $code) {
            $row = $db->fetch(
                'SELECT id, status, warehouse_id, product_id FROM product_serials
                 WHERE product_id = ? AND (imei = ? OR serial_no = ?) LIMIT 1',
                [$productId, $code, $code]
            );
            if (!$row) {
                throw new \InvalidArgumentException('IMEI/serial not found for this product: ' . $code);
            }
            if ($row['status'] !== 'available') {
                throw new \InvalidArgumentException('IMEI/serial is not available (' . $row['status'] . '): ' . $code);
            }
            if ((int) $row['warehouse_id'] !== $warehouseId) {
                throw new \InvalidArgumentException('IMEI/serial is in another warehouse: ' . $code);
            }
            $db->update('product_serials', [
                'status'       => 'sold',
                'sale_item_id' => $saleItemId,
            ], ['id' => (int) $row['id']]);
        }
    }

    /**
     * Restore sold serials linked to a sale item (delete sale / exchange return).
     *
     * @return array<int,string> Restored IMEI / serial codes
     */
    public static function restoreForSaleItem(int $saleItemId, int $quantity, string $status = 'available'): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT id, imei, serial_no FROM product_serials WHERE sale_item_id = ? AND status = \'sold\' ORDER BY id DESC LIMIT ' . (int) max(0, (int) $quantity),
            [$saleItemId]
        );
        $codes = [];
        foreach ($rows as $row) {
            $db->update('product_serials', [
                'status'       => $status,
                'sale_item_id' => null,
            ], ['id' => (int) $row['id']]);
            $code = (string) ($row['imei'] ?: $row['serial_no'] ?? '');
            if ($code !== '') {
                $codes[] = $code;
            }
        }
        return $codes;
    }

    /**
     * Restore by explicit codes (optional helper for returns).
     *
     * @param array<int,string> $codes
     */
    public static function restoreByCodes(int $productId, int $warehouseId, array $codes, string $status = 'available'): void
    {
        $codes = self::parseList($codes);
        $db = Database::getInstance();
        foreach ($codes as $code) {
            $row = $db->fetch(
                'SELECT id FROM product_serials WHERE product_id = ? AND (imei = ? OR serial_no = ?) LIMIT 1',
                [$productId, $code, $code]
            );
            if (!$row) {
                throw new \InvalidArgumentException('IMEI/serial not found: ' . $code);
            }
            $db->update('product_serials', [
                'status'       => $status,
                'sale_item_id' => null,
                'warehouse_id' => $warehouseId,
            ], ['id' => (int) $row['id']]);
        }
    }

    /**
     * Move available serials between warehouses.
     *
     * @param array<int,string> $codes
     */
    public static function transfer(int $productId, int $fromWarehouse, int $toWarehouse, array $codes): void
    {
        $codes = self::parseList($codes);
        if ($codes === []) {
            throw new \InvalidArgumentException('Select IMEI / serial numbers to transfer.');
        }
        $db = Database::getInstance();
        foreach ($codes as $code) {
            $row = $db->fetch(
                'SELECT id, status, warehouse_id FROM product_serials
                 WHERE product_id = ? AND (imei = ? OR serial_no = ?) LIMIT 1',
                [$productId, $code, $code]
            );
            if (!$row || $row['status'] !== 'available' || (int) $row['warehouse_id'] !== $fromWarehouse) {
                throw new \InvalidArgumentException('Cannot transfer IMEI/serial: ' . $code);
            }
            $db->update('product_serials', ['warehouse_id' => $toWarehouse], ['id' => (int) $row['id']]);
        }
    }

    /**
     * Mark available units as damaged / removed on stock subtraction.
     *
     * @param array<int,string> $codes
     */
    public static function markDamaged(int $productId, int $warehouseId, array $codes): void
    {
        $codes = self::parseList($codes);
        $db = Database::getInstance();
        foreach ($codes as $code) {
            $row = $db->fetch(
                'SELECT id, status, warehouse_id FROM product_serials
                 WHERE product_id = ? AND (imei = ? OR serial_no = ?) LIMIT 1',
                [$productId, $code, $code]
            );
            if (!$row || $row['status'] !== 'available' || (int) $row['warehouse_id'] !== $warehouseId) {
                throw new \InvalidArgumentException('Cannot remove IMEI/serial: ' . $code);
            }
            $db->update('product_serials', ['status' => 'damaged'], ['id' => (int) $row['id']]);
        }
    }

    /**
     * Validate that qty matches IMEI count for tracked products.
     *
     * @param array<int,string> $codes
     */
    public static function assertQtyMatches(int $productId, float $qty, array $codes): void
    {
        if (!self::productTracksUnits($productId)) {
            return;
        }
        $codes = self::parseList($codes);
        $need = (int) round($qty);
        if ((float) $need !== $qty) {
            throw new \InvalidArgumentException('IMEI-tracked products must use whole-unit quantities.');
        }
        if (count($codes) !== $need) {
            throw new \InvalidArgumentException(
                'IMEI count (' . count($codes) . ') must equal quantity (' . $need . ').'
            );
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listAvailable(int $productId, int $warehouseId): array
    {
        return (new ProductSerial())->availableForProduct($productId, $warehouseId);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function searchAvailable(
        int $productId,
        int $warehouseId,
        string $keyword = '',
        int $limit = 20
    ): array {
        return (new ProductSerial())->searchAvailable($productId, $warehouseId, $keyword, $limit);
    }
}
