<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\PaymentService;
use App\Services\SaleService;
use App\Services\SerialService;
use App\Services\StockService;

/**
 * Sales management: list, create, view, invoice, returns.
 *
 * @package App\Controllers
 */
final class SaleController extends Controller
{
    private Sale $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Sale();
    }

    public function index(): void
    {
        $this->authorize('sales.view');
        $keyword = $this->request->string('q');
        $status  = $this->request->string('status');
        $result  = $this->model->search($keyword, $status, $this->request->int('page', 1));
        $this->view('sales.index', [
            'title'   => 'Sales',
            'sales'   => $result['data'],
            'meta'    => $result,
            'keyword' => $keyword,
            'status'  => $status,
        ]);
    }

    public function create(): void
    {
        $this->authorize('sales.create');
        $this->view('sales.form', [
            'title'      => 'New Sale',
            'customers'  => (new Customer())->all(['status' => 1], 'name'),
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('sales.create');
        $this->verifyCsrf();

        $items = $this->parseItems();
        if ($items === []) {
            flash('error', 'Add at least one product.');
            redirect('sales/create');
        }

        try {
            $type = $this->request->string('type', 'retail');
            if ($type === 'wholesale' && !can('products.wholesale.view')) {
                $type = 'retail';
            }

            $result = SaleService::create([
                'customer_id'    => $this->request->int('customer_id') ?: null,
                'warehouse_id'   => $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId(),
                'type'           => $type,
                'discount'       => $this->request->float('discount'),
                'discount_type'  => $this->request->string('discount_type', 'fixed'),
                'tax'            => $this->request->float('tax'),
                'vat'            => $this->request->float('vat'),
                'shipping'       => $this->request->float('shipping'),
                'paid'           => $this->request->float('paid'),
                'payment_method' => $this->request->string('payment_method', 'cash'),
                'note'           => $this->request->string('note') ?: null,
                'sale_date'      => $this->request->string('sale_date', date('Y-m-d')),
                'items'          => $items,
            ]);
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('sales/create');
        }

        flash('success', 'Sale ' . $result['invoice_no'] . ' completed.');
        redirect('sales/' . $result['id']);
    }

    public function show(string $id): void
    {
        $this->authorize('sales.view');
        $sale = $this->model->findDetailed((int) $id);
        if (!$sale) {
            Response::abort(404);
        }
        $items = $this->model->items((int) $id);
        $returnable = array_filter($items, static fn (array $it): bool => (float) $it['returnable_qty'] > 0);
        $this->view('sales.show', [
            'title'           => $sale['invoice_no'],
            'sale'            => $sale,
            'items'           => $items,
            'exchanges'       => $this->model->exchangesForSale((int) $id),
            'exchangeContext' => $this->model->exchangeContext((int) $id),
            'canExchange'     => $returnable !== [] && ($sale['status'] ?? '') !== 'cancelled',
        ]);
    }

    public function exchangeForm(string $id): void
    {
        $this->authorize('sales.exchange');
        $sale = $this->model->findDetailed((int) $id);
        if (!$sale) {
            Response::abort(404);
        }
        if (($sale['status'] ?? '') === 'cancelled') {
            flash('error', 'Cannot exchange a cancelled sale.');
            redirect('sales/' . $id);
        }
        $items = $this->model->items((int) $id);
        $returnable = array_values(array_filter($items, static fn (array $it): bool => (float) $it['returnable_qty'] > 0));
        if ($returnable === []) {
            flash('error', 'No returnable items left on this sale.');
            redirect('sales/' . $id);
        }
        $this->view('sales.exchange', [
            'title'      => 'Exchange · ' . $sale['invoice_no'],
            'sale'       => $sale,
            'items'      => $returnable,
        ]);
    }

    public function exchangeStore(string $id): void
    {
        $this->authorize('sales.exchange');
        $this->verifyCsrf();

        $returnItems = [];
        $saleItemIds = (array) $this->request->input('sale_item_id', []);
        $returnQtys  = (array) $this->request->input('return_qty', []);
        foreach ($saleItemIds as $i => $itemId) {
            $itemId = (int) $itemId;
            $qty = (float) ($returnQtys[$i] ?? 0);
            if ($itemId > 0 && $qty > 0) {
                $returnItems[] = ['sale_item_id' => $itemId, 'quantity' => $qty];
            }
        }

        $newItems = [];
        $productIds = (array) $this->request->input('product_id', []);
        $quantities = (array) $this->request->input('quantity', []);
        $prices     = (array) $this->request->input('unit_price', []);
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (float) ($quantities[$i] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $newItems[] = [
                'product_id' => $pid,
                'quantity'   => $qty,
                'unit_price' => (float) ($prices[$i] ?? 0),
            ];
        }

        try {
            $result = \App\Services\ExchangeService::process([
                'original_sale_id' => (int) $id,
                'return_items'     => $returnItems,
                'new_items'        => $newItems,
                'paid'             => $this->request->float('paid'),
                'payment_method'   => $this->request->string('payment_method', 'cash'),
                'reason'           => $this->request->string('reason') ?: null,
            ]);
        } catch (\InvalidArgumentException $e) {
            flash('error', $e->getMessage());
            redirect('sales/' . $id . '/exchange');
        } catch (\Throwable $e) {
            flash('error', 'Exchange failed: ' . $e->getMessage());
            redirect('sales/' . $id . '/exchange');
        }

        $msg = 'Exchange ' . $result['reference'] . ' completed. New invoice ' . $result['new_invoice'] . '.';
        if ($result['settlement'] === 'customer_pays' && $result['amount_paid'] > 0) {
            $msg .= ' Collected ' . money($result['amount_paid']) . '.';
        } elseif ($result['settlement'] === 'refund' && $result['amount_refunded'] > 0) {
            $msg .= ' Refunded ' . money($result['amount_refunded']) . '.';
        } else {
            $msg .= ' Even exchange.';
        }
        flash('success', $msg);
        redirect('sales/' . $result['new_sale_id']);
    }

    public function addPayment(string $id): void
    {
        $this->authorize('sales.payment');
        $this->verifyCsrf();
        $sale = $this->model->find((int) $id);
        if (!$sale) {
            Response::abort(404);
        }
        $amount = min($this->request->float('amount'), (float) $sale['due']);
        if ($amount <= 0) {
            flash('error', 'Invalid amount.');
            redirect('sales/' . $id);
        }
        Database::getInstance()->transaction(function () use ($id, $sale, $amount): void {
            $newDue = (float) $sale['due'] - $amount;
            Database::getInstance()->update('sales', [
                'paid'           => (float) $sale['paid'] + $amount,
                'due'            => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
            ], ['id' => (int) $id]);
            PaymentService::record('sale', (int) $id, 'in', $amount, $this->request->string('method', 'cash'), 'customer', (int) $sale['customer_id'], 'Sale payment');
        });
        flash('success', 'Payment recorded.');
        redirect('sales/' . $id);
    }

    public function returnItems(string $id): void
    {
        $this->authorize('sales.return');
        $this->verifyCsrf();
        flash('info', 'Use Exchange to return items and issue replacements in one step.');
        redirect('sales/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->authorize('sales.delete');
        $this->verifyCsrf();
        $sale = $this->model->findDetailed((int) $id);
        if ($sale) {
            Database::getInstance()->transaction(function () use ($id, $sale): void {
                foreach ($this->model->items((int) $id) as $item) {
                    $restored = SerialService::restoreForSaleItem((int) $item['id'], (int) round((float) $item['quantity']), 'available');
                    StockService::increase(
                        (int) $item['product_id'],
                        (int) $sale['warehouse_id'],
                        (float) $item['quantity'],
                        'adjustment',
                        'sale_delete',
                        (int) $id,
                        'Sale deleted',
                        null,
                        $restored
                    );
                }
                $this->model->delete((int) $id);
            });
            ActivityLog::record('sale.deleted', 'Sales', 'Deleted sale ' . $sale['invoice_no']);
        }
        flash('success', 'Sale deleted and stock restored.');
        redirect('sales');
    }

    public function invoice(string $id): void
    {
        $this->renderDoc((int) $id, 'sales.invoice');
    }

    public function thermal(string $id): void
    {
        $this->renderDoc((int) $id, 'sales.thermal');
    }

    public function pdf(string $id): void
    {
        $this->authorize('sales.invoice');
        $sale = $this->model->findDetailed((int) $id);
        if (!$sale) {
            Response::abort(404);
        }
        $html = \App\Core\View::partial('sales.invoice', [
            'sale'            => $sale,
            'items'           => $this->model->items((int) $id),
            'forPdf'          => true,
            'exchangeContext' => $this->model->exchangeContext((int) $id),
        ]);
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream('Invoice-' . $sale['invoice_no'] . '.pdf', ['Attachment' => false]);
        exit;
    }

    private function renderDoc(int $id, string $view): void
    {
        $this->authorize('sales.invoice');
        $sale = $this->model->findDetailed($id);
        if (!$sale) {
            Response::abort(404);
        }
        $this->bare($view, [
            'title'           => $sale['invoice_no'],
            'sale'            => $sale,
            'items'           => $this->model->items($id),
            'exchangeContext' => $this->model->exchangeContext($id),
        ]);
    }

    /**
     * @return array<int,array{product_id:int,quantity:float,unit_price:float,imeis:array<int,string>}>
     */
    private function parseItems(): array
    {
        $productIds = (array) $this->request->input('product_id', []);
        $quantities = (array) $this->request->input('quantity', []);
        $prices     = (array) $this->request->input('unit_price', []);
        $imeiLists  = (array) $this->request->input('imei_list', []);
        $items      = [];
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (float) ($quantities[$i] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $items[] = [
                'product_id' => $pid,
                'quantity'   => $qty,
                'unit_price' => (float) ($prices[$i] ?? 0),
                'imeis'      => SerialService::parseList((string) ($imeiLists[$i] ?? '')),
            ];
        }
        return $items;
    }
}
