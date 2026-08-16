<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/**
 * Centralised stock movement logic.
 *
 * Every quantity change flows through here so the `stock` table and the
 * `stock_logs` audit trail always stay consistent.
 *
 * @package App\Services
 */
final class StockService
{
    /**
     * Increase stock for a product in a warehouse and log the movement.
     *
     * @param array<int,string> $imeis
     */
    public static function increase(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $type = 'purchase',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $variantId = null,
        array $imeis = []
    ): void {
        self::move($productId, $warehouseId, abs($quantity), $type, $referenceType, $referenceId, $note, $variantId, $imeis);
    }

    /**
     * Decrease stock for a product in a warehouse and log the movement.
     *
     * @param array<int,string> $imeis
     */
    public static function decrease(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $type = 'sale',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $variantId = null,
        array $imeis = []
    ): void {
        self::move($productId, $warehouseId, -abs($quantity), $type, $referenceType, $referenceId, $note, $variantId, $imeis);
    }

    /**
     * @param array<int,string> $imeis
     */
    private static function move(
        int $productId,
        int $warehouseId,
        float $delta,
        string $type,
        ?string $referenceType,
        ?int $referenceId,
        ?string $note,
        ?int $variantId,
        array $imeis = []
    ): void {
        $db = Database::getInstance();

        $existing = $db->fetch(
            'SELECT id, quantity FROM stock WHERE product_id = ? AND warehouse_id = ? AND (variant_id <=> ?) AND (batch_no IS NULL) LIMIT 1',
            [$productId, $warehouseId, $variantId]
        );

        if ($existing) {
            $newQty = (float) $existing['quantity'] + $delta;
            $db->update('stock', ['quantity' => $newQty], ['id' => $existing['id']]);
        } else {
            $newQty = $delta;
            $db->insert('stock', [
                'product_id'   => $productId,
                'variant_id'   => $variantId,
                'warehouse_id' => $warehouseId,
                'quantity'     => $newQty,
            ]);
        }

        $imeiCodes = SerialService::parseList($imeis);
        $imeiText  = $imeiCodes === [] ? null : implode(', ', $imeiCodes);
        if ($imeiText !== null) {
            $suffix = 'IMEI/Serial: ' . $imeiText;
            $note = $note !== null && $note !== '' ? ($note . ' · ' . $suffix) : $suffix;
        }

        $row = [
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'warehouse_id'   => $warehouseId,
            'type'           => $type,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'quantity'       => $delta,
            'balance_after'  => $newQty,
            'note'           => $note,
            'created_by'     => Auth::id(),
            'created_at'     => now(),
        ];

        // Optional column (added by apply_imei.php) — keep note as fallback.
        if (self::hasImeiCodesColumn()) {
            $row['imei_codes'] = $imeiText;
        }

        $db->insert('stock_logs', $row);
    }

    private static ?bool $hasImeiCol = null;

    private static function hasImeiCodesColumn(): bool
    {
        if (self::$hasImeiCol !== null) {
            return self::$hasImeiCol;
        }
        try {
            $cols = Database::getInstance()->fetchAll('SHOW COLUMNS FROM stock_logs LIKE \'imei_codes\'');
            self::$hasImeiCol = $cols !== [];
        } catch (\Throwable) {
            self::$hasImeiCol = false;
        }
        return self::$hasImeiCol;
    }

    /**
     * Current on-hand quantity for a product (optionally per warehouse).
     */
    public static function available(int $productId, ?int $warehouseId = null): float
    {
        $db = Database::getInstance();
        if ($warehouseId !== null) {
            return (float) $db->scalar(
                'SELECT COALESCE(SUM(quantity),0) FROM stock WHERE product_id = ? AND warehouse_id = ?',
                [$productId, $warehouseId]
            );
        }
        return (float) $db->scalar('SELECT COALESCE(SUM(quantity),0) FROM stock WHERE product_id = ?', [$productId]);
    }
}
