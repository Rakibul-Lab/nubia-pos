<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\SaleService;

/**
 * Point of Sale terminal.
 *
 * @package App\Controllers
 */
final class PosController extends Controller
{
    public function index(): void
    {
        $this->authorize('pos.use');
        $warehouse   = new Warehouse();
        $warehouseId = $warehouse->defaultId();
        $products    = (new Product())->quickSearch('', $warehouseId, 60, true);
        $products    = strip_product_costs_list($products);

        $this->view('pos.index', [
            'title'      => 'POS Terminal',
            'products'   => $products,
            'categories' => (new Category())->all(['status' => 1], 'name'),
            'customers'  => (new Customer())->all(['status' => 1], 'name'),
            'warehouses' => $warehouse->all(['status' => 1], 'name'),
            'defaultWarehouse' => $warehouseId,
        ], 'layouts/app');
    }

    public function checkout(): void
    {
        $this->authorize('pos.checkout');
        $this->verifyCsrf();

        $items = json_decode((string) $this->request->input('items', '[]'), true);
        if (!is_array($items) || $items === []) {
            Response::error('Cart is empty.');
        }

        $normalized = [];
        foreach ($items as $item) {
            $normalized[] = [
                'product_id' => (int) ($item['id'] ?? 0),
                'quantity'   => (float) ($item['qty'] ?? 0),
                'unit_price' => (float) ($item['price'] ?? 0),
                'imeis'      => $item['imeis'] ?? [],
            ];
        }

        try {
            $result = SaleService::create([
                'customer_id'    => $this->request->int('customer_id') ?: null,
                'warehouse_id'   => $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId(),
                'type'           => 'pos',
                'discount'       => $this->request->float('discount'),
                'discount_type'  => $this->request->string('discount_type', 'fixed'),
                'tax'            => $this->request->float('tax'),
                'shipping'       => 0,
                'paid'           => $this->request->float('paid'),
                'payment_method' => $this->request->string('payment_method', 'cash'),
                'items'          => $normalized,
            ]);
        } catch (\Throwable $e) {
            Response::error('Checkout failed: ' . $e->getMessage());
        }

        Response::success('Sale completed.', [
            'sale'        => $result,
            'invoice_url' => url('sales/' . $result['id']),
            'thermal_url' => url('sales/' . $result['id'] . '/thermal'),
        ]);
    }
}
