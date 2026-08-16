<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ActivityLog;

/**
 * Encapsulates sale creation so POS and standard sales share one code path.
 *
 * @package App\Services
 */
final class SaleService
{
    /**
     * Create a sale with items, stock deduction, profit calc and payment.
     *
     * @param array{
     *     customer_id?:int|null, warehouse_id?:int|null, type?:string,
     *     discount?:float, discount_type?:string, tax?:float, vat?:float,
     *     shipping?:float, paid?:float, credit_applied?:float, payment_method?:string, note?:string|null,
     *     coupon_code?:string|null, sale_date?:string,
     *     items:array<int,array{product_id:int,quantity:float,unit_price:float,unit_cost?:float,imeis?:array<int,string>|string}>
     * } $payload
     *
     * @return array{id:int,invoice_no:string,total:float,paid:float,due:float,change:float,profit:float}
     */
    public static function create(array $payload): array
    {
        $db = Database::getInstance();

        return $db->transaction(function () use ($db, $payload): array {
            $items       = $payload['items'];
            $warehouseId = (int) ($payload['warehouse_id'] ?? 1);
            $discount    = (float) ($payload['discount'] ?? 0);
            $discType    = $payload['discount_type'] ?? 'fixed';
            $tax         = (float) ($payload['tax'] ?? 0);
            $vat         = (float) ($payload['vat'] ?? 0);
            $shipping    = (float) ($payload['shipping'] ?? 0);
            $paidInput   = (float) ($payload['paid'] ?? 0);
            $creditApplied = max(0, (float) ($payload['credit_applied'] ?? 0));

            $subtotal  = 0.0;
            $totalCost = 0.0;
            $normItems = [];

            foreach ($items as $item) {
                $pid   = (int) $item['product_id'];
                $qty   = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                if ($pid <= 0 || $qty <= 0) {
                    continue;
                }
                // Resolve cost price for profit calculation.
                $product = $db->fetch(
                    'SELECT cost_price, name, has_imei, has_serial FROM products WHERE id = ? LIMIT 1',
                    [$pid]
                );
                $cost = isset($item['unit_cost'])
                    ? (float) $item['unit_cost']
                    : (float) ($product['cost_price'] ?? 0);

                $imeis = SerialService::parseList($item['imeis'] ?? []);
                if (SerialService::productTracksUnits($product ?? $pid)) {
                    SerialService::assertQtyMatches($pid, $qty, $imeis);
                    if ($imeis === []) {
                        throw new \InvalidArgumentException(
                            'IMEI required for product: ' . ($product['name'] ?? ('#' . $pid))
                        );
                    }
                }

                $lineSubtotal = round($qty * $price, 2);
                $subtotal    += $lineSubtotal;
                $totalCost   += round($qty * $cost, 2);

                $normItems[] = [
                    'product_id' => $pid,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'unit_cost'  => $cost,
                    'subtotal'   => $lineSubtotal,
                    'imeis'      => $imeis,
                ];
            }

            if ($normItems === []) {
                throw new \InvalidArgumentException('Add at least one product.');
            }

            $discountValue = $discType === 'percent' ? round($subtotal * $discount / 100, 2) : $discount;
            $total = max(0, $subtotal - $discountValue + $tax + $vat + $shipping);
            $profit = round($total - $totalCost - $tax - $vat - $shipping, 2);

            // Cash collected + exchange/return credit applied toward the invoice.
            $paidCash = max(0, $paidInput);
            $paid     = min($paidCash + $creditApplied, $total);
            $change   = max(0, ($paidCash + $creditApplied) - $total);
            $due      = max(0, $total - $paid);
            $customerId = !empty($payload['customer_id']) ? (int) $payload['customer_id'] : null;
            $method = (string) ($payload['payment_method'] ?? 'cash');
            if ($creditApplied > 0 && $paidCash <= 0 && $due <= 0) {
                $method = $method === 'credit' ? 'mixed' : $method;
            }

            // Due / credit sales require a registered customer for ledger tracking.
            if ($due > 0 && $customerId === null) {
                throw new \InvalidArgumentException(
                    'Due amount is only allowed for registered customers. Select a customer or collect full payment.'
                );
            }
            if ($method === 'credit' && $customerId === null) {
                throw new \InvalidArgumentException(
                    'Credit payment requires a registered customer. Select a customer from the list.'
                );
            }

            $paymentStatus = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
            $invoiceNo     = self::nextInvoiceNo();

            $saleId = $db->insert('sales', [
                'invoice_no'     => $invoiceNo,
                'customer_id'    => $customerId,
                'warehouse_id'   => $warehouseId,
                'sale_date'      => $payload['sale_date'] ?? date('Y-m-d'),
                'type'           => $payload['type'] ?? 'pos',
                'status'         => 'completed',
                'subtotal'       => $subtotal,
                'discount'       => $discountValue,
                'discount_type'  => $discType,
                'coupon_code'    => $payload['coupon_code'] ?? null,
                'tax'            => $tax,
                'vat'            => $vat,
                'shipping'       => $shipping,
                'total'          => $total,
                'total_cost'     => $totalCost,
                'profit'         => $profit,
                'paid'           => $paid,
                'due'            => $due,
                'change_amount'  => $change,
                'payment_status' => $paymentStatus,
                'payment_method' => $method === 'credit' && $paidCash <= 0 ? 'credit' : ($creditApplied > 0 && $paidCash > 0 ? 'mixed' : $method),
                'note'           => $payload['note'] ?? null,
                'created_by'     => Auth::id(),
            ]);

            foreach ($normItems as $item) {
                $saleItemId = $db->insert('sale_items', [
                    'sale_id'    => $saleId,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost'  => $item['unit_cost'],
                    'subtotal'   => $item['subtotal'],
                ]);
                if ($item['imeis'] !== []) {
                    SerialService::markSold(
                        $item['product_id'],
                        $warehouseId,
                        $item['imeis'],
                        (int) $saleItemId
                    );
                }
                StockService::decrease(
                    $item['product_id'], $warehouseId, $item['quantity'],
                    'sale', 'sale', (int) $saleId, 'Sale ' . $invoiceNo, null, $item['imeis']
                );
            }

            // Only record real cash/card money collected (not exchange credit).
            $payMethod = $method === 'credit' || $method === 'mixed' ? 'cash' : $method;
            if ($paidCash > 0 && $payMethod !== 'credit') {
                $allowed = ['cash', 'card', 'bank', 'mobile', 'bkash', 'nagad', 'upay', 'dutch_bangla', 'cheque'];
                if (!in_array($payMethod, $allowed, true)) {
                    $payMethod = 'cash';
                }
                PaymentService::record(
                    'sale', (int) $saleId, 'in', min($paidCash, $total),
                    $payMethod, 'customer',
                    $customerId, $creditApplied > 0 ? 'Sale payment (exchange balance)' : 'Sale payment'
                );
            }

            ActivityLog::record('sale.created', 'Sales', 'Sale ' . $invoiceNo . ' for ' . money($total), 'sale', (int) $saleId);

            return [
                'id'         => (int) $saleId,
                'invoice_no' => $invoiceNo,
                'total'      => $total,
                'paid'       => $paid,
                'due'        => $due,
                'change'     => $change,
                'profit'     => $profit,
            ];
        });
    }

    private static function nextInvoiceNo(): string
    {
        $prefix = setting('invoice_prefix', 'INV');
        $count  = (int) Database::getInstance()->scalar('SELECT COUNT(*) FROM sales') + 1;
        return sprintf('%s-%s-%05d', $prefix, date('ym'), $count);
    }
}
