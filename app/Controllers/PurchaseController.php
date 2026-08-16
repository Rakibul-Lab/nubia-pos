<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PaymentService;
use App\Services\SerialService;
use App\Services\StockService;

/**
 * Purchase management: create, view, payment, return.
 *
 * @package App\Controllers
 */
final class PurchaseController extends Controller
{
    private Purchase $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Purchase();
    }

    public function index(): void
    {
        $this->authorize('purchases.view');
        $keyword = $this->request->string('q');
        $status  = $this->request->string('status');
        $result  = $this->model->search($keyword, $status, $this->request->int('page', 1));
        $this->view('purchases.index', [
            'title'     => 'Purchases',
            'purchases' => $result['data'],
            'meta'      => $result,
            'keyword'   => $keyword,
            'status'    => $status,
        ]);
    }

    public function create(): void
    {
        $this->authorize('purchases.create');
        $this->view('purchases.form', [
            'title'      => 'New Purchase',
            'suppliers'  => (new Supplier())->all(['status' => 1], 'name'),
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
        ]);
    }

    public function show(string $id): void
    {
        $this->authorize('purchases.view');
        $purchase = $this->model->findDetailed((int) $id);
        if (!$purchase) {
            Response::abort(404);
        }
        $this->view('purchases.show', [
            'title'    => 'Purchase ' . $purchase['reference'],
            'purchase' => $purchase,
            'items'    => $this->model->items((int) $id),
        ]);
    }

    public function store(): void
    {
        $this->authorize('purchases.create');
        $this->verifyCsrf();

        try {
            $items = $this->parseItems();
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('purchases/create');
        }
        if ($items === []) {
            flash('error', 'Add at least one product to the purchase.');
            redirect('purchases/create');
        }

        $supplierId  = $this->request->int('supplier_id') ?: null;
        $warehouseId = $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId();
        $discount    = $this->request->float('discount');
        $tax         = $this->request->float('tax');
        $shipping    = $this->request->float('shipping');
        $paid        = $this->request->float('paid');

        $subtotal = array_sum(array_map(static fn ($i) => $i['subtotal'], $items));
        $total    = max(0, $subtotal - $discount + $tax + $shipping);
        $due      = max(0, $total - $paid);

        try {
            $purchaseId = Database::getInstance()->transaction(function () use ($items, $supplierId, $warehouseId, $discount, $tax, $shipping, $paid, $subtotal, $total, $due) {
            $reference = generate_code('PUR');
            $id = $this->model->create([
                'reference'      => $reference,
                'supplier_id'    => $supplierId,
                'warehouse_id'   => $warehouseId,
                'purchase_date'  => $this->request->string('purchase_date', date('Y-m-d')),
                'status'         => 'received',
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'shipping'       => $shipping,
                'total'          => $total,
                'paid'           => $paid,
                'due'            => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'note'           => $this->request->string('note') ?: null,
                'created_by'     => Auth::id(),
            ]);

            foreach ($items as $item) {
                $itemId = Database::getInstance()->insert('purchase_items', [
                    'purchase_id' => $id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_cost'   => $item['unit_cost'],
                    'subtotal'    => $item['subtotal'],
                ]);
                if ($item['imeis'] !== []) {
                    SerialService::stockIn(
                        $item['product_id'],
                        $warehouseId,
                        $item['imeis'],
                        (int) $itemId,
                        SerialService::prefersImei($item['product_id'])
                    );
                }
                StockService::increase($item['product_id'], $warehouseId, $item['quantity'], 'purchase', 'purchase', (int) $id, 'Purchase ' . $reference, null, $item['imeis']);
                // Keep product cost price current.
                Database::getInstance()->update('products', ['cost_price' => $item['unit_cost']], ['id' => $item['product_id']]);
            }

            if ($paid > 0) {
                PaymentService::record('purchase', (int) $id, 'out', $paid, $this->request->string('payment_method', 'cash'), 'supplier', $supplierId, 'Purchase payment');
            }
            return $id;
        });
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('purchases/create');
        }

        ActivityLog::record('purchase.created', 'Purchases', 'Created purchase for ' . money($total), 'purchase', (int) $purchaseId);
        flash('success', 'Purchase recorded successfully.');
        redirect('purchases/' . $purchaseId);
    }

    public function addPayment(string $id): void
    {
        $this->authorize('purchases.payment');
        $this->verifyCsrf();
        $purchase = $this->model->find((int) $id);
        if (!$purchase) {
            Response::abort(404);
        }
        $amount = min($this->request->float('amount'), (float) $purchase['due']);
        if ($amount <= 0) {
            flash('error', 'Invalid payment amount.');
            redirect('purchases/' . $id);
        }

        Database::getInstance()->transaction(function () use ($id, $purchase, $amount): void {
            $newDue = (float) $purchase['due'] - $amount;
            Database::getInstance()->update('purchases', [
                'paid'           => (float) $purchase['paid'] + $amount,
                'due'            => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
            ], ['id' => (int) $id]);
            PaymentService::record('purchase', (int) $id, 'out', $amount, $this->request->string('method', 'cash'), 'supplier', (int) $purchase['supplier_id'], 'Purchase payment');
        });

        flash('success', 'Payment recorded.');
        redirect('purchases/' . $id);
    }

    public function returnItems(string $id): void
    {
        $this->authorize('purchases.return');
        $this->verifyCsrf();
        $purchase = $this->model->findDetailed((int) $id);
        if (!$purchase) {
            Response::abort(404);
        }
        $amount = $this->request->float('amount');
        Database::getInstance()->transaction(function () use ($id, $purchase, $amount): void {
            $ref = generate_code('PRET');
            Database::getInstance()->insert('purchase_returns', [
                'reference'   => $ref,
                'purchase_id' => (int) $id,
                'total'       => $amount,
                'reason'      => $this->request->string('reason') ?: null,
                'created_by'  => Auth::id(),
            ]);
            // Reduce stock for returned items (simplified: reduce a chosen product qty).
            $productId = $this->request->int('product_id');
            $qty       = $this->request->float('quantity');
            if ($productId && $qty > 0) {
                StockService::decrease($productId, (int) $purchase['warehouse_id'], $qty, 'return_out', 'purchase_return', (int) $id, 'Purchase return ' . $ref);
            }
        });
        flash('success', 'Purchase return recorded.');
        redirect('purchases/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->authorize('purchases.delete');
        $this->verifyCsrf();
        $purchase = $this->model->findDetailed((int) $id);
        if ($purchase) {
            Database::getInstance()->transaction(function () use ($id, $purchase): void {
                foreach ($this->model->items((int) $id) as $item) {
                    StockService::decrease((int) $item['product_id'], (int) $purchase['warehouse_id'], (float) $item['quantity'], 'adjustment', 'purchase_delete', (int) $id, 'Purchase deleted');
                }
                $this->model->delete((int) $id);
            });
        }
        flash('success', 'Purchase deleted and stock reversed.');
        redirect('purchases');
    }

    public function invoice(string $id): void
    {
        $this->authorize('purchases.invoice');
        $this->authorize('costs.view');
        $purchase = $this->model->findDetailed((int) $id);
        if (!$purchase) {
            Response::abort(404);
        }
        $this->bare('purchases.invoice', [
            'title'    => 'Purchase ' . $purchase['reference'],
            'purchase' => $purchase,
            'items'    => $this->model->items((int) $id),
        ]);
    }

    /**
     * Parse repeating item[] inputs into a normalised array.
     *
     * @return array<int,array{product_id:int,quantity:float,unit_cost:float,subtotal:float,imeis:array<int,string>}>
     */
    private function parseItems(): array
    {
        $productIds = (array) $this->request->input('product_id', []);
        $quantities = (array) $this->request->input('quantity', []);
        $costs      = (array) $this->request->input('unit_cost', []);
        $imeiLists  = (array) $this->request->input('imei_list', []);
        $items      = [];

        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (float) ($quantities[$i] ?? 0);
            $cost = (float) ($costs[$i] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $imeis = SerialService::parseList((string) ($imeiLists[$i] ?? ''));
            if (SerialService::productTracksUnits($pid)) {
                SerialService::assertQtyMatches($pid, $qty, $imeis);
            } else {
                $imeis = [];
            }
            $items[] = [
                'product_id' => $pid,
                'quantity'   => $qty,
                'unit_cost'  => $cost,
                'subtotal'   => round($qty * $cost, 2),
                'imeis'      => $imeis,
            ];
        }
        return $items;
    }
}
