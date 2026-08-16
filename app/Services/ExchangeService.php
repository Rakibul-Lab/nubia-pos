<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ActivityLog;

/**
 * Product exchange: return items from an original sale and issue replacements.
 *
 * Settlement:
 * - new_total > return_credit → customer pays the difference
 * - new_total < return_credit → refund the difference
 * - equal → even swap (credit covers new sale)
 *
 * @package App\Services
 */
final class ExchangeService
{
    /**
     * @param array{
     *     original_sale_id:int,
     *     return_items:array<int,array{sale_item_id:int,quantity:float}>,
     *     new_items:array<int,array{product_id:int,quantity:float,unit_price:float}>,
     *     paid?:float,
     *     payment_method?:string,
     *     reason?:string|null
     * } $payload
     *
     * @return array{
     *     exchange_id:int, reference:string, original_sale_id:int, new_sale_id:int,
     *     new_invoice:string, return_total:float, new_total:float, difference:float,
     *     settlement:string, amount_paid:float, amount_refunded:float
     * }
     */
    public static function process(array $payload): array
    {
        $db = Database::getInstance();

        return $db->transaction(function () use ($db, $payload): array {
            $saleId = (int) ($payload['original_sale_id'] ?? 0);
            $sale = $db->fetch('SELECT * FROM sales WHERE id = ? LIMIT 1', [$saleId]);
            if (!$sale) {
                throw new \InvalidArgumentException('Original sale not found.');
            }
            if (($sale['status'] ?? '') === 'cancelled') {
                throw new \InvalidArgumentException('Cannot exchange a cancelled sale.');
            }

            $returnLines = self::normalizeReturnItems($db, $saleId, $payload['return_items'] ?? []);
            if ($returnLines === []) {
                throw new \InvalidArgumentException('Select at least one item to return.');
            }

            $newItems = [];
            foreach ($payload['new_items'] ?? [] as $item) {
                $pid = (int) ($item['product_id'] ?? 0);
                $qty = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['unit_price'] ?? 0);
                if ($pid <= 0 || $qty <= 0) {
                    continue;
                }
                $newItems[] = [
                    'product_id' => $pid,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                ];
            }
            if ($newItems === []) {
                throw new \InvalidArgumentException('Add at least one replacement product.');
            }

            $returnTotal = round(array_sum(array_column($returnLines, 'subtotal')), 2);
            $warehouseId = (int) $sale['warehouse_id'];
            $customerId  = !empty($sale['customer_id']) ? (int) $sale['customer_id'] : null;
            $reason      = trim((string) ($payload['reason'] ?? '')) ?: null;
            $method      = (string) ($payload['payment_method'] ?? 'cash');
            $paidInput   = max(0, (float) ($payload['paid'] ?? 0));

            // --- Return leg ---
            $returnRef = generate_code('SRET');
            $returnId = $db->insert('sale_returns', [
                'reference'  => $returnRef,
                'sale_id'    => $saleId,
                'total'      => $returnTotal,
                'reason'     => $reason ?: 'Exchange',
                'created_by' => Auth::id(),
            ]);

            foreach ($returnLines as $line) {
                $db->insert('sale_return_items', [
                    'sale_return_id' => $returnId,
                    'sale_item_id'   => $line['sale_item_id'],
                    'product_id'     => $line['product_id'],
                    'quantity'       => $line['quantity'],
                    'unit_price'     => $line['unit_price'],
                    'subtotal'       => $line['subtotal'],
                ]);
                $db->query(
                    'UPDATE sale_items SET returned_qty = returned_qty + ? WHERE id = ?',
                    [$line['quantity'], $line['sale_item_id']]
                );
                $restored = SerialService::restoreForSaleItem(
                    $line['sale_item_id'],
                    $line['quantity'],
                    'available'
                );
                StockService::increase(
                    $line['product_id'],
                    $warehouseId,
                    $line['quantity'],
                    'return_in',
                    'sale_return',
                    (int) $returnId,
                    'Exchange return ' . $returnRef,
                    null,
                    $restored
                );
            }

            // --- New sale leg (credit from returned goods) ---
            $newTotalPreview = round(array_sum(array_map(
                static fn (array $i): float => round($i['quantity'] * $i['unit_price'], 2),
                $newItems
            )), 2);

            $difference = round($newTotalPreview - $returnTotal, 2);
            $creditApplied = min($returnTotal, $newTotalPreview);
            $amountPaid = 0.0;
            $amountRefunded = 0.0;
            $settlement = 'even';

            if ($difference > 0) {
                $settlement = 'customer_pays';
                $amountPaid = $paidInput > 0 ? $paidInput : $difference;
                // Walk-in must cover the full difference (no due).
                if ($customerId === null && $amountPaid + 0.0001 < $difference) {
                    throw new \InvalidArgumentException(
                        'Collect the full difference (' . money($difference) . ') for walk-in exchanges.'
                    );
                }
                if ($method === 'credit' && $customerId === null) {
                    throw new \InvalidArgumentException('Credit settlement requires a registered customer.');
                }
            } elseif ($difference < 0) {
                $settlement = 'refund';
                $amountRefunded = abs($difference);
                $amountPaid = 0.0;
            } else {
                $amountPaid = 0.0;
            }

            $cashTowardSale = $settlement === 'customer_pays' ? $amountPaid : 0.0;
            if ($method === 'credit' && $settlement === 'customer_pays') {
                $cashTowardSale = 0.0;
                $amountPaid = 0.0;
            }

            $saleResult = SaleService::create([
                'customer_id'    => $customerId,
                'warehouse_id'   => $warehouseId,
                'type'           => $sale['type'] ?? 'retail',
                'discount'       => 0,
                'discount_type'  => 'fixed',
                'tax'            => 0,
                'vat'            => 0,
                'shipping'       => 0,
                'paid'           => $cashTowardSale,
                'credit_applied' => $creditApplied,
                'payment_method' => $settlement === 'customer_pays' && $cashTowardSale <= 0 ? 'credit' : $method,
                'note'           => 'Exchange from ' . $sale['invoice_no'] . ($reason ? ' — ' . $reason : ''),
                'sale_date'      => date('Y-m-d'),
                'items'          => $newItems,
            ]);

            $newSaleId = (int) $saleResult['id'];
            $newTotal  = (float) $saleResult['total'];
            $difference = round($newTotal - $returnTotal, 2);

            // Refund excess return credit
            if ($amountRefunded > 0) {
                $refundMethod = $method;
                $allowed = ['cash', 'card', 'bank', 'mobile', 'bkash', 'nagad', 'upay', 'dutch_bangla', 'cheque'];
                if (!in_array($refundMethod, $allowed, true)) {
                    $refundMethod = 'cash';
                }
                PaymentService::record(
                    'sale',
                    $saleId,
                    'out',
                    $amountRefunded,
                    $refundMethod,
                    'customer',
                    $customerId,
                    'Exchange refund vs ' . $saleResult['invoice_no']
                );
            }

            $exchangeRef = generate_code('EXC');
            $cashCollected = $settlement === 'customer_pays'
                ? max(0, round((float) $saleResult['paid'] - $creditApplied, 2))
                : 0.0;

            $exchangeId = $db->insert('sale_exchanges', [
                'reference'        => $exchangeRef,
                'original_sale_id' => $saleId,
                'new_sale_id'      => $newSaleId,
                'sale_return_id'   => $returnId,
                'return_total'     => $returnTotal,
                'new_total'        => $newTotal,
                'difference'       => $difference,
                'settlement'       => $settlement,
                'amount_paid'      => $cashCollected,
                'amount_refunded'  => $amountRefunded,
                'payment_method'   => $method,
                'reason'           => $reason,
                'created_by'       => Auth::id(),
            ]);

            ActivityLog::record(
                'sale.exchange',
                'Sales',
                'Exchange ' . $exchangeRef . ': ' . $sale['invoice_no'] . ' → ' . $saleResult['invoice_no'],
                'sale',
                $saleId
            );

            return [
                'exchange_id'      => (int) $exchangeId,
                'reference'        => $exchangeRef,
                'original_sale_id' => $saleId,
                'new_sale_id'      => $newSaleId,
                'new_invoice'      => $saleResult['invoice_no'],
                'return_total'     => $returnTotal,
                'new_total'        => $newTotal,
                'difference'       => $difference,
                'settlement'       => $settlement,
                'amount_paid'      => round($cashCollected, 2),
                'amount_refunded'  => $amountRefunded,
            ];
        });
    }

    /**
     * @param array<int,array{sale_item_id:int,quantity:float}> $raw
     * @return array<int,array{sale_item_id:int,product_id:int,quantity:float,unit_price:float,subtotal:float}>
     */
    private static function normalizeReturnItems(Database $db, int $saleId, array $raw): array
    {
        $out = [];
        foreach ($raw as $row) {
            $itemId = (int) ($row['sale_item_id'] ?? 0);
            $qty = (float) ($row['quantity'] ?? 0);
            if ($itemId <= 0 || $qty <= 0) {
                continue;
            }
            $item = $db->fetch(
                'SELECT * FROM sale_items WHERE id = ? AND sale_id = ? LIMIT 1',
                [$itemId, $saleId]
            );
            if (!$item) {
                throw new \InvalidArgumentException('Invalid sale item for return.');
            }
            $available = (float) $item['quantity'] - (float) $item['returned_qty'];
            if ($qty > $available + 0.0001) {
                throw new \InvalidArgumentException(
                    'Return qty exceeds available for a line item (max ' . rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.') . ').'
                );
            }
            $unitPrice = (float) $item['unit_price'];
            $out[] = [
                'sale_item_id' => $itemId,
                'product_id'   => (int) $item['product_id'],
                'quantity'     => $qty,
                'unit_price'   => $unitPrice,
                'subtotal'     => round($qty * $unitPrice, 2),
            ];
        }
        return $out;
    }
}
