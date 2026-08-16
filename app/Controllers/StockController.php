<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Warehouse;
use App\Services\SerialService;
use App\Services\StockService;

/**
 * Stock overview, adjustments, transfers & history.
 *
 * @package App\Controllers
 */
final class StockController extends Controller
{
    public function index(): void
    {
        $this->authorize('stock.view');
        $keyword     = $this->request->string('q');
        $warehouseId = $this->request->int('warehouse') ?: null;
        $like        = '%' . $keyword . '%';

        $join   = 'LEFT JOIN stock s ON s.product_id = p.id';
        $params = [$like, $like];
        if ($warehouseId) {
            $join     = 'LEFT JOIN stock s ON s.product_id = p.id AND s.warehouse_id = ?';
            $params   = [$warehouseId, $like, $like];
        }

        $rows = Database::getInstance()->fetchAll(
            'SELECT p.id, p.name, p.sku, p.alert_quantity, u.short_name AS unit,
                    COALESCE(SUM(s.quantity),0) AS stock
             FROM products p
             ' . $join . '
             LEFT JOIN units u ON u.id = p.unit_id
             WHERE p.status = 1 AND (p.name LIKE ? OR p.sku LIKE ?)
             GROUP BY p.id, p.name, p.sku, p.alert_quantity, u.short_name
             ORDER BY stock ASC LIMIT 100',
            $params
        );

        $this->view('stock.index', [
            'title'       => 'Stock',
            'rows'        => $rows,
            'keyword'     => $keyword,
            'warehouses'  => (new Warehouse())->all(['status' => 1], 'name'),
            'warehouseId' => $warehouseId,
        ]);
    }

    public function adjustmentForm(): void
    {
        $this->authorize('stock.adjust');
        $this->view('stock.adjustment', [
            'title'      => 'Stock Adjustment',
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
        ]);
    }

    public function storeAdjustment(): void
    {
        $this->authorize('stock.adjust');
        $this->verifyCsrf();
        $productId   = $this->request->int('product_id');
        $warehouseId = $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId();
        $qty         = $this->request->float('quantity');
        $type        = $this->request->string('type', 'addition');

        if ($productId <= 0 || $qty <= 0) {
            flash('error', 'Select a product and enter a valid quantity.');
            redirect('stock/adjustment');
        }

        try {
            Database::getInstance()->transaction(function () use ($productId, $warehouseId, $qty, $type): void {
                $ref = generate_code('ADJ');
                Database::getInstance()->insert('stock_adjustments', [
                    'reference'    => $ref,
                    'warehouse_id' => $warehouseId,
                    'type'         => $type,
                    'reason'       => $this->request->string('reason') ?: null,
                    'created_by'   => Auth::id(),
                ]);
                $imeis = SerialService::parseList($this->request->string('imei_list'));
                if (SerialService::productTracksUnits($productId)) {
                    SerialService::assertQtyMatches($productId, $qty, $imeis);
                    if ($type === 'addition') {
                        SerialService::stockIn($productId, $warehouseId, $imeis, null, SerialService::prefersImei($productId));
                        StockService::increase($productId, $warehouseId, $qty, 'adjustment', 'adjustment', 0, 'Adjustment ' . $ref, null, $imeis);
                    } else {
                        SerialService::markDamaged($productId, $warehouseId, $imeis);
                        StockService::decrease($productId, $warehouseId, $qty, 'damage', 'adjustment', 0, 'Adjustment ' . $ref, null, $imeis);
                    }
                    return;
                }
                if ($type === 'addition') {
                    StockService::increase($productId, $warehouseId, $qty, 'adjustment', 'adjustment', 0, 'Adjustment ' . $ref);
                } else {
                    StockService::decrease($productId, $warehouseId, $qty, 'adjustment', 'adjustment', 0, 'Adjustment ' . $ref);
                }
            });
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('stock/adjustment');
        }

        flash('success', 'Stock adjusted successfully.');
        redirect('stock');
    }

    public function transferForm(): void
    {
        $this->authorize('stock.transfer');
        $this->view('stock.transfer', [
            'title'      => 'Stock Transfer',
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
        ]);
    }

    public function storeTransfer(): void
    {
        $this->authorize('stock.transfer');
        $this->verifyCsrf();
        $productId = $this->request->int('product_id');
        $from      = $this->request->int('from_warehouse');
        $to        = $this->request->int('to_warehouse');
        $qty       = $this->request->float('quantity');

        if ($productId <= 0 || $qty <= 0 || $from === $to) {
            flash('error', 'Provide valid product, quantity and different warehouses.');
            redirect('stock/transfer');
        }

        try {
            Database::getInstance()->transaction(function () use ($productId, $from, $to, $qty): void {
                $ref = generate_code('TRF');
                Database::getInstance()->insert('stock_transfers', [
                    'reference'      => $ref,
                    'from_warehouse' => $from,
                    'to_warehouse'   => $to,
                    'note'           => $this->request->string('note') ?: null,
                    'status'         => 'completed',
                    'created_by'     => Auth::id(),
                ]);
                $imeis = SerialService::parseList($this->request->string('imei_list'));
                if (SerialService::productTracksUnits($productId)) {
                    SerialService::assertQtyMatches($productId, $qty, $imeis);
                    SerialService::transfer($productId, $from, $to, $imeis);
                }
                StockService::decrease($productId, $from, $qty, 'transfer_out', 'transfer', 0, 'Transfer ' . $ref, null, $imeis);
                StockService::increase($productId, $to, $qty, 'transfer_in', 'transfer', 0, 'Transfer ' . $ref, null, $imeis);
            });
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('stock/transfer');
        }

        flash('success', 'Stock transferred successfully.');
        redirect('stock');
    }

    public function history(): void
    {
        $this->authorize('stock.history');
        $logs = Database::getInstance()->fetchAll(
            'SELECT sl.*, p.name AS product_name, w.name AS warehouse_name, u.name AS user_name
             FROM stock_logs sl
             JOIN products p ON p.id = sl.product_id
             LEFT JOIN warehouses w ON w.id = sl.warehouse_id
             LEFT JOIN users u ON u.id = sl.created_by
             ORDER BY sl.id DESC LIMIT 200'
        );
        $this->view('stock.history', ['title' => 'Stock History', 'logs' => $logs]);
    }
}
