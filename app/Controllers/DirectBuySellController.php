<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\DirectTransaction;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PaymentService;
use App\Services\StockService;

/**
 * Direct Buy & Sell — the flagship feature.
 *
 * When a customer requests an out-of-stock product, the shop buys it from a
 * supplier and immediately sells it. This records the purchase, the sale, the
 * cost breakdown and the resulting profit in a single transaction. Optionally
 * the item can be added to inventory.
 *
 * Profit = Selling Price − (Purchase Price + Transportation + Other Costs)
 *
 * @package App\Controllers
 */
final class DirectBuySellController extends Controller
{
    private DirectTransaction $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DirectTransaction();
    }

    public function index(): void
    {
        $this->authorize('direct.view');
        $result = $this->model->recent($this->request->int('page', 1));

        $stats = Database::getInstance()->fetch(
            'SELECT COUNT(*) AS cnt'
            . (Auth::can('revenue.view') ? ', COALESCE(SUM(selling_price*quantity),0) AS revenue' : ', 0 AS revenue')
            . (Auth::can('profit.view') ? ', COALESCE(SUM(profit),0) AS profit' : ', 0 AS profit') . '
             FROM direct_transactions'
        );

        $this->view('direct.index', [
            'title'        => 'Direct Buy & Sell',
            'transactions' => $result['data'],
            'meta'         => $result,
            'stats'        => $stats,
        ]);
    }

    public function create(): void
    {
        $this->authorize('direct.create');
        $this->view('direct.form', [
            'title'      => 'Direct Buy & Sell',
            'suppliers'  => (new Supplier())->all(['status' => 1], 'name'),
            'customers'  => (new Customer())->all(['status' => 1], 'name'),
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
        ]);
    }

    public function show(string $id): void
    {
        $this->authorize('direct.view');
        $txn = $this->model->find((int) $id);
        if (!$txn) {
            Response::abort(404);
        }
        $this->view('direct.show', ['title' => $txn['reference'], 'txn' => $txn]);
    }

    public function store(): void
    {
        $this->authorize('direct.create');
        $this->verifyCsrf();

        $data = $this->validate([
            'product_name'   => 'required|max:200',
            'selling_price'  => 'required|numeric',
            'purchase_price' => 'required|numeric',
        ], ['product_name' => 'Product Name']);

        $quantity       = max(1, $this->request->float('quantity', 1));
        $purchasePrice  = $this->request->float('purchase_price');
        $transportation = $this->request->float('transportation_cost');
        $additional     = $this->request->float('additional_cost');
        $sellingPrice   = $this->request->float('selling_price');
        $addToInventory = $this->request->bool('add_to_inventory');

        // Per-unit and totals.
        $totalCost   = ($purchasePrice + $transportation + $additional) * $quantity;
        $totalRevenue = $sellingPrice * $quantity;
        $profit      = round($totalRevenue - $totalCost, 2);

        $paid = $this->request->float('paid', $totalRevenue);
        $due  = max(0, $totalRevenue - $paid);

        $supplierId = $this->request->int('supplier_id') ?: null;
        $customerId = $this->request->int('customer_id') ?: null;

        $txnId = Database::getInstance()->transaction(function () use (
            $data, $quantity, $purchasePrice, $transportation, $additional, $sellingPrice,
            $addToInventory, $totalCost, $totalRevenue, $profit, $paid, $due, $supplierId, $customerId
        ) {
            $reference = generate_code('DBS');

            // 1) Purchase record.
            $warehouseId = $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId();
            $purchaseId  = Database::getInstance()->insert('purchases', [
                'reference'      => 'P-' . $reference,
                'supplier_id'    => $supplierId,
                'warehouse_id'   => $warehouseId,
                'purchase_date'  => date('Y-m-d'),
                'status'         => 'received',
                'subtotal'       => $purchasePrice * $quantity,
                'shipping'       => $transportation,
                'tax'            => $additional,
                'total'          => $totalCost,
                'paid'           => $totalCost,
                'due'            => 0,
                'payment_status' => 'paid',
                'note'           => 'Direct Buy & Sell purchase',
                'created_by'     => Auth::id(),
            ]);

            // 2) Sale record.
            $invoicePrefix = setting('invoice_prefix', 'INV');
            $saleId = Database::getInstance()->insert('sales', [
                'invoice_no'     => 'S-' . $reference,
                'customer_id'    => $customerId,
                'warehouse_id'   => $warehouseId,
                'sale_date'      => date('Y-m-d'),
                'type'           => 'retail',
                'status'         => 'completed',
                'subtotal'       => $totalRevenue,
                'total'          => $totalRevenue,
                'total_cost'     => $totalCost,
                'profit'         => $profit,
                'paid'           => $paid,
                'due'            => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'payment_method' => $this->request->string('payment_method', 'cash'),
                'note'           => 'Direct Buy & Sell sale',
                'created_by'     => Auth::id(),
            ]);

            // 3) Optionally create/increase inventory.
            $productId = null;
            if ($addToInventory) {
                $productId = Database::getInstance()->insert('products', [
                    'name'          => $data['product_name'],
                    'slug'          => slugify((string) $data['product_name']) . '-' . substr($reference, -5),
                    'sku'           => 'DBS-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
                    'cost_price'    => $purchasePrice + $transportation + $additional,
                    'selling_price' => $sellingPrice,
                    'status'        => 1,
                    'created_by'    => Auth::id(),
                ]);
                // Add then immediately sell (net zero, but full audit trail).
                StockService::increase((int) $productId, $warehouseId, $quantity, 'purchase', 'direct', 0, 'Direct B&S purchase ' . $reference);
                StockService::decrease((int) $productId, $warehouseId, $quantity, 'direct_sell', 'direct', 0, 'Direct B&S sale ' . $reference);
            }

            // 4) Direct transaction summary record.
            $id = $this->model->create([
                'reference'           => $reference,
                'product_name'        => $data['product_name'],
                'product_id'          => $productId,
                'supplier_id'         => $supplierId,
                'supplier_name'       => $this->request->string('supplier_name') ?: null,
                'customer_id'         => $customerId,
                'customer_name'       => $this->request->string('customer_name') ?: null,
                'customer_phone'      => $this->request->string('customer_phone') ?: null,
                'quantity'            => $quantity,
                'purchase_price'      => $purchasePrice,
                'transportation_cost' => $transportation,
                'additional_cost'     => $additional,
                'selling_price'       => $sellingPrice,
                'total_cost'          => $totalCost,
                'profit'              => $profit,
                'add_to_inventory'    => $addToInventory ? 1 : 0,
                'payment_method'      => $this->request->string('payment_method', 'cash'),
                'paid'                => $paid,
                'due'                 => $due,
                'purchase_id'         => $purchaseId,
                'sale_id'             => $saleId,
                'note'                => $this->request->string('note') ?: null,
                'created_by'          => Auth::id(),
            ]);

            // 5) Payments (money out to supplier, money in from customer).
            PaymentService::record('purchase', (int) $purchaseId, 'out', $totalCost, 'cash', 'supplier', $supplierId, 'Direct B&S purchase');
            if ($paid > 0) {
                PaymentService::record('sale', (int) $saleId, 'in', $paid, $this->request->string('payment_method', 'cash'), 'customer', $customerId, 'Direct B&S sale');
            }

            return $id;
        });

        ActivityLog::record(
            'direct.created',
            'Direct',
            Auth::can('profit.view')
                ? 'Direct Buy & Sell profit ' . money($profit)
                : 'Direct Buy & Sell completed',
            'direct',
            (int) $txnId
        );
        flash(
            'success',
            Auth::can('profit.view')
                ? 'Direct Buy & Sell completed. Profit: ' . money($profit)
                : 'Direct Buy & Sell completed.'
        );
        redirect('direct-buy-sell/' . $txnId);
    }

    public function invoice(string $id): void
    {
        $this->authorize('direct.invoice');
        $txn = $this->model->find((int) $id);
        if (!$txn) {
            Response::abort(404);
        }
        $this->bare('direct.invoice', ['title' => $txn['reference'], 'txn' => $txn]);
    }
}
