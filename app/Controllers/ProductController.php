<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\SerialService;
use App\Services\StockService;

/**
 * Product CRUD, search, barcode & QR generation.
 *
 * @package App\Controllers
 */
final class ProductController extends Controller
{
    private Product $products;

    public function __construct()
    {
        parent::__construct();
        $this->products = new Product();
    }

    public function index(): void
    {
        $this->authorize('products.view');
        $keyword     = $this->request->string('q');
        $categoryId  = $this->request->int('category') ?: null;
        $warehouseId = $this->request->int('warehouse') ?: null;
        $page        = $this->request->int('page', 1);

        $result = $this->products->search($keyword, $categoryId, $page, 10, $warehouseId);

        $this->view('products.index', [
            'title'       => 'Products',
            'products'    => strip_product_costs_list($result['data']),
            'meta'        => $result,
            'keyword'     => $keyword,
            'categories'  => (new Category())->all(['status' => 1], 'name'),
            'categoryId'  => $categoryId,
            'warehouses'  => (new Warehouse())->all(['status' => 1], 'name'),
            'warehouseId' => $warehouseId,
        ]);
    }

    public function export(): void
    {
        $this->authorize('products.export');
        $format     = strtolower($this->request->string('format', 'excel'));
        $keyword    = $this->request->string('q');
        $categoryId = $this->request->int('category') ?: null;

        $rows = $this->products->exportRows($keyword, $categoryId);
        $columns = [
            'name'            => 'Product',
            'sku'             => 'SKU',
            'barcode'         => 'Barcode',
            'category_name'   => 'Category',
            'brand_name'      => 'Brand',
            'unit'            => 'Unit',
            'selling_price'   => 'Selling Price (inc.vat)',
            'wholesale_price' => 'Wholesale (inc.vat)',
            'stock'           => 'Stock',
            'alert_quantity'  => 'Alert Qty',
            'status'          => 'Status',
        ];
        if (Auth::can('costs.view')) {
            $columns = array_merge(
                array_slice($columns, 0, 6, true),
                ['cost_price' => 'Cost (inc.vat)'],
                array_slice($columns, 6, null, true)
            );
        }
        if (!Auth::can('products.wholesale.view')) {
            unset($columns['wholesale_price']);
        }
        $title = 'Products';

        if ($format === 'pdf') {
            $this->exportPdf($title, $rows, $columns);
        }
        $this->exportExcel($title, $rows, $columns);
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,string> $columns
     */
    private function exportExcel(string $title, array $rows, array $columns): never
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');
        $col = 1;
        foreach ($columns as $label) {
            $sheet->setCellValue([$col++, 1], $label);
        }
        $rowIdx = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach (array_keys($columns) as $key) {
                $sheet->setCellValue([$col++, $rowIdx], $row[$key] ?? '');
            }
            $rowIdx++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . slugify($title) . '-' . date('Ymd') . '.xlsx"');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,string> $columns
     */
    private function exportPdf(string $title, array $rows, array $columns): never
    {
        $html = '<h2 style="font-family:Arial;margin:0 0 12px;">' . e($title) . '</h2>'
            . '<div style="font-family:Arial;font-size:11px;color:#64748b;margin-bottom:12px;">Exported ' . date('Y-m-d H:i') . ' · ' . count($rows) . ' products</div>'
            . '<table width="100%" style="border-collapse:collapse;font-family:Arial;font-size:10px;"><thead><tr>';
        foreach ($columns as $label) {
            $html .= '<th style="border:1px solid #ccc;padding:5px;background:#f1f5f9;text-align:left;">' . e($label) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (array_keys($columns) as $key) {
                $html .= '<td style="border:1px solid #eee;padding:5px;">' . e((string) ($row[$key] ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(slugify($title) . '-' . date('Ymd') . '.pdf', ['Attachment' => true]);
        exit;
    }

    public function create(): void
    {
        $this->authorize('products.create');
        $this->view('products.form', $this->formData(null));
    }

    public function edit(string $id): void
    {
        $this->authorize('products.edit');
        $product = $this->products->findDetailed((int) $id);
        if (!$product) {
            Response::abort(404);
        }
        $this->view('products.form', $this->formData($product));
    }

    public function show(string $id): void
    {
        $this->authorize('products.view');
        $product = $this->products->findDetailed((int) $id);
        if (!$product) {
            Response::abort(404);
        }
        $stockRows = Auth::can('stock.view')
            ? Database::getInstance()->fetchAll(
                'SELECT s.*, w.name AS warehouse_name FROM stock s JOIN warehouses w ON w.id = s.warehouse_id WHERE s.product_id = ?',
                [(int) $id]
            )
            : [];

        $perPage = 5;
        $serialsPage = max(1, $this->request->int('serials_page', 1));
        $logsPage    = max(1, $this->request->int('logs_page', 1));

        $serialResult = ['data' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1];
        $availCount = 0;
        if (Auth::can('products.serials.view')) {
            $serialModel = new ProductSerial();
            $serialResult = $serialModel->paginateForProduct((int) $id, $serialsPage, $perPage);
            $availCount = $serialModel->availableCount((int) $id);
        }

        $logsTotal = Auth::can('stock.history')
            ? (int) Database::getInstance()->scalar(
                'SELECT COUNT(*) FROM stock_logs WHERE product_id = ?',
                [(int) $id]
            )
            : 0;
        $logsLast = (int) max(1, (int) ceil($logsTotal / $perPage));
        if ($logsPage > $logsLast) {
            $logsPage = $logsLast;
        }
        $logsOffset = ($logsPage - 1) * $perPage;
        $logs = Auth::can('stock.history')
            ? Database::getInstance()->fetchAll(
                'SELECT * FROM stock_logs WHERE product_id = ? ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . $logsOffset,
                [(int) $id]
            )
            : [];

        $this->view('products.show', [
            'title'        => $product['name'],
            'product'      => strip_product_wholesale(strip_product_costs($product)),
            'stockRows'    => $stockRows,
            'logs'         => $logs,
            'logsMeta'     => [
                'total'     => $logsTotal,
                'page'      => $logsPage,
                'per_page'  => $perPage,
                'last_page' => $logsLast,
            ],
            'serials'      => $serialResult['data'],
            'serialsMeta'  => $serialResult,
            'serialsAvail' => $availCount,
            'warehouses'   => Auth::can('products.serials.manage')
                ? (new Warehouse())->all(['status' => 1], 'name')
                : [],
        ]);
    }

    public function store(): void
    {
        $this->authorize('products.create');
        $this->verifyCsrf();

        $data = $this->validate([
            'name'          => 'required|min:2|max:200',
            'sku'           => 'required|max:80|unique:products,sku',
            'selling_price' => 'required|numeric',
        ], ['sku' => 'SKU']);

        try {
            $productId = Database::getInstance()->transaction(function () use ($data) {
                $payload = $this->payload($data);
                $id      = $this->products->create($payload);

                $tracks = ((int) $payload['has_imei'] === 1) || ((int) $payload['has_serial'] === 1);
                $imeis  = SerialService::parseList($this->request->string('imei_list'));
                $warehouseId = $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId();

                if ($tracks) {
                    if ($imeis === []) {
                        // Allow create without stock; IMEIs can be added later / via purchase.
                        return $id;
                    }
                    SerialService::assertQtyMatches((int) $id, (float) count($imeis), $imeis);
                    SerialService::stockIn(
                        (int) $id,
                        $warehouseId,
                        $imeis,
                        null,
                        (int) $payload['has_imei'] === 1
                    );
                    StockService::increase((int) $id, $warehouseId, (float) count($imeis), 'opening', 'product', (int) $id, 'Opening stock (IMEI)', null, $imeis);
                    return $id;
                }

                $opening = $this->request->float('opening_stock');
                if ($opening > 0) {
                    StockService::increase((int) $id, $warehouseId, $opening, 'opening', 'product', (int) $id, 'Opening stock');
                }
                return $id;
            });
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('products/create');
        }

        ActivityLog::record('product.created', 'Products', 'Created product ' . $data['name'], 'product', (int) $productId);
        flash('success', 'Product created successfully.');
        redirect('products/' . $productId);
    }

    public function update(string $id): void
    {
        $this->authorize('products.edit');
        $this->verifyCsrf();
        $product = $this->products->find((int) $id);
        if (!$product) {
            Response::abort(404);
        }

        $data = $this->validate([
            'name'          => 'required|min:2|max:200',
            'sku'           => 'required|max:80|unique:products,sku,' . $id,
            'selling_price' => 'required|numeric',
        ], ['sku' => 'SKU']);

        $this->products->update((int) $id, $this->payload($data, $product));
        ActivityLog::record('product.updated', 'Products', 'Updated product ' . $data['name'], 'product', (int) $id);
        flash('success', 'Product updated successfully.');
        redirect('products/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->authorize('products.delete');
        $this->verifyCsrf();
        $this->products->delete((int) $id);
        ActivityLog::record('product.deleted', 'Products', 'Deleted product #' . $id, 'product', (int) $id);
        if ($this->request->wantsJson()) {
            Response::success('Product deleted.');
        }
        flash('success', 'Product deleted.');
        redirect('products');
    }

    public function apiSearch(): void
    {
        $this->authorize('products.search');
        $keyword     = $this->request->string('q');
        $warehouseId = $this->request->int('warehouse');
        $limit       = $this->request->int('limit');
        $limit       = $limit > 0 ? min($limit, 100) : 20;
        $inStockOnly = $this->request->int('in_stock') === 1;
        $results     = $this->products->quickSearch($keyword, $warehouseId, $limit, $inStockOnly);
        // Never leak buying/cost prices without costs.view (purchase creators may keep defaults).
        $results = strip_product_costs_list($results, true);
        Response::json(['results' => $results]);
    }

    /**
     * Add IMEI / serial units to an existing product (increases stock).
     */
    public function addSerials(string $id): void
    {
        $this->authorize('products.serials.manage');
        $this->verifyCsrf();
        $product = $this->products->find((int) $id);
        if (!$product) {
            Response::abort(404);
        }
        if (!SerialService::productTracksUnits($product)) {
            flash('error', 'Enable Track IMEI or Track Serial on this product first.');
            redirect('products/' . $id);
        }

        $imeis = SerialService::parseList($this->request->string('imei_list'));
        if ($imeis === []) {
            flash('error', 'Enter at least one IMEI / serial number.');
            redirect('products/' . $id);
        }

        $warehouseId = $this->request->int('warehouse_id') ?: (new Warehouse())->defaultId();

        try {
            Database::getInstance()->transaction(function () use ($id, $product, $imeis, $warehouseId): void {
                SerialService::stockIn(
                    (int) $id,
                    $warehouseId,
                    $imeis,
                    null,
                    SerialService::prefersImei($product)
                );
                StockService::increase(
                    (int) $id,
                    $warehouseId,
                    (float) count($imeis),
                    'adjustment',
                    'product',
                    (int) $id,
                    'IMEI stock-in',
                    null,
                    $imeis
                );
            });
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('products/' . $id);
        }

        ActivityLog::record('product.serials_added', 'Products', 'Added ' . count($imeis) . ' IMEI(s) to ' . $product['name'], 'product', (int) $id);
        flash('success', count($imeis) . ' IMEI/serial(s) added to stock.');
        redirect('products/' . $id);
    }

    /**
     * Replace the code on a single unit — used to swap out placeholders
     * created by database/backfill_serials.php for the real IMEI.
     */
    public function updateSerial(string $id, string $serialId): void
    {
        $this->authorize('products.serials.manage');
        $this->verifyCsrf();

        $product = $this->products->find((int) $id);
        $serials = new ProductSerial();
        $serial  = $serials->find((int) $serialId);

        if (!$product || !$serial || (int) $serial['product_id'] !== (int) $id) {
            Response::abort(404);
        }
        if ($serial['status'] !== 'available') {
            flash('error', 'Only available units can be edited.');
            redirect('products/' . $id);
        }

        $codes = SerialService::parseList($this->request->string('code'));
        $code  = $codes[0] ?? '';
        if ($code === '') {
            flash('error', 'Enter an IMEI / serial number.');
            redirect('products/' . $id);
        }

        $old = (string) ($serial['imei'] ?: $serial['serial_no']);
        if ($code !== $old) {
            $taken = Database::getInstance()->scalar(
                'SELECT COUNT(*) FROM product_serials WHERE (imei = ? OR serial_no = ?) AND id <> ?',
                [$code, $code, (int) $serialId]
            );
            if ((int) $taken > 0) {
                flash('error', 'That IMEI / serial is already in the system: ' . $code);
                redirect('products/' . $id);
            }
        }

        $asImei = SerialService::prefersImei($product);
        $serials->update((int) $serialId, [
            'imei'      => $asImei ? $code : null,
            'serial_no' => $asImei ? null : $code,
        ]);

        ActivityLog::record(
            'product.serial_updated',
            'Products',
            'Changed unit ' . $old . ' to ' . $code . ' on ' . $product['name'],
            'product',
            (int) $id
        );
        flash('success', 'Unit updated to ' . $code . '.');
        redirect('products/' . $id);
    }

    /**
     * Remove an available unit and decrease stock by one.
     */
    public function destroySerial(string $id, string $serialId): void
    {
        $this->authorize('products.serials.manage');
        $this->verifyCsrf();

        $product = $this->products->find((int) $id);
        $serials = new ProductSerial();
        $serial  = $serials->find((int) $serialId);

        if (!$product || !$serial || (int) $serial['product_id'] !== (int) $id) {
            Response::abort(404);
        }
        if ($serial['status'] !== 'available') {
            flash('error', 'Only available units can be removed.');
            redirect('products/' . $id);
        }

        $code = (string) ($serial['imei'] ?: $serial['serial_no']);

        Database::getInstance()->transaction(function () use ($serials, $serialId, $serial, $id, $code): void {
            $serials->delete((int) $serialId);
            StockService::decrease(
                (int) $id,
                (int) $serial['warehouse_id'],
                1.0,
                'adjustment',
                'product',
                (int) $id,
                'IMEI removed',
                null,
                [$code]
            );
        });

        ActivityLog::record('product.serial_removed', 'Products', 'Removed unit ' . $code . ' from ' . $product['name'], 'product', (int) $id);
        flash('success', 'Unit ' . $code . ' removed.');
        redirect('products/' . $id);
    }

    /**
     * Available IMEIs for a product in a warehouse (POS / purchase helpers).
     */
    public function apiSerials(string $id): void
    {
        $this->authorize('products.serials.view');
        $warehouseId = $this->request->int('warehouse') ?: (new Warehouse())->defaultId();
        $keyword     = $this->request->string('q');
        $limit       = $this->request->int('limit');
        $limit       = $limit > 0 ? min($limit, 100) : 20;

        $list  = SerialService::searchAvailable((int) $id, $warehouseId, $keyword, $limit);
        $total = (new ProductSerial())->availableCount((int) $id, $warehouseId);

        Response::json([
            'serials'   => $list,
            'count'     => count($list),
            'available' => $total,
        ]);
    }

    public function barcode(string $id): void
    {
        $this->authorize('products.labels');
        $product = $this->products->find((int) $id);
        if (!$product) {
            Response::abort(404);
        }
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $code      = $product['barcode'] ?: $product['sku'];
        $png       = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 60);
        header('Content-Type: image/png');
        echo $png;
        exit;
    }

    public function qrcode(string $id): void
    {
        $this->authorize('products.labels');
        $product = $this->products->findDetailed((int) $id);
        if (!$product) {
            Response::abort(404);
        }
        $payload = json_encode([
            'sku'   => $product['sku'],
            'name'  => $product['name'],
            'price' => $product['selling_price'],
        ]);
        $result = \Endroid\QrCode\Builder\Builder::create()
            ->data((string) $payload)
            ->size(240)
            ->margin(10)
            ->build();
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
        exit;
    }

    /**
     * Barcode image whose encoded value is one unit's IMEI / serial.
     */
    public function serialBarcode(string $id, string $serialId): void
    {
        $this->authorize('products.labels');
        $unit = $this->findProductSerial((int) $id, (int) $serialId);
        $code = (string) ($unit['imei'] ?: $unit['serial_no']);

        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $png = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 60);
        header('Content-Type: image/png');
        header('Cache-Control: private, max-age=3600');
        echo $png;
        exit;
    }

    /**
     * QR image whose payload uniquely identifies one IMEI / serial unit.
     */
    public function serialQrcode(string $id, string $serialId): void
    {
        $this->authorize('products.labels');
        $unit = $this->findProductSerial((int) $id, (int) $serialId);
        // Encode the raw unit code so POS/camera scans resolve it directly.
        $code = (string) ($unit['imei'] ?: $unit['serial_no']);

        $result = \Endroid\QrCode\Builder\Builder::create()
            ->data($code)
            ->size(240)
            ->margin(10)
            ->build();
        header('Content-Type: ' . $result->getMimeType());
        header('Cache-Control: private, max-age=3600');
        echo $result->getString();
        exit;
    }

    /**
     * Printable barcode + QR label for one IMEI / serial unit.
     */
    public function serialLabel(string $id, string $serialId): void
    {
        $this->authorize('products.labels');
        $product = $this->products->findDetailed((int) $id);
        if (!$product) {
            Response::abort(404);
        }
        $unit = $this->findProductSerial((int) $id, (int) $serialId);
        $type = strtolower($this->request->string('type', 'both'));
        if (!in_array($type, ['barcode', 'qr', 'both'], true)) {
            $type = 'both';
        }
        $copies = max(1, min(50, $this->request->int('copies', 1)));

        echo \App\Core\View::partial('products.labels', [
            'title'      => 'Print IMEI Label · ' . $product['name'],
            'product'    => $product,
            'type'       => $type,
            'copies'     => $copies,
            'code'       => $unit['imei'] ?: $unit['serial_no'],
            'serialUnit' => $unit,
        ]);
        exit;
    }

    /**
     * Printable product labels: barcode, QR, or both.
     */
    public function labels(string $id): void
    {
        $this->authorize('products.labels');
        $product = $this->products->findDetailed((int) $id);
        if (!$product) {
            Response::abort(404);
        }

        $type = strtolower($this->request->string('type', 'both'));
        if (!in_array($type, ['barcode', 'qr', 'both'], true)) {
            $type = 'both';
        }
        $copies = max(1, min(50, $this->request->int('copies', 1)));
        $serialUnits = [];
        if (SerialService::productTracksUnits($product)) {
            $serialUnits = (new ProductSerial())->forProduct((int) $id);
        }

        echo \App\Core\View::partial('products.labels', [
            'title'       => 'Print Labels · ' . $product['name'],
            'product'     => $product,
            'type'        => $type,
            'copies'      => $copies,
            'code'        => $product['barcode'] ?: $product['sku'],
            'serialUnits' => $serialUnits,
        ]);
        exit;
    }

    /**
     * Find an IMEI / serial and ensure it belongs to the requested product.
     *
     * @return array<string,mixed>
     */
    private function findProductSerial(int $productId, int $serialId): array
    {
        $unit = Database::getInstance()->fetch(
            'SELECT ps.*, p.name AS product_name, p.sku
             FROM product_serials ps
             JOIN products p ON p.id = ps.product_id
             WHERE ps.id = ? AND ps.product_id = ? LIMIT 1',
            [$serialId, $productId]
        );
        if (!$unit || (!$unit['imei'] && !$unit['serial_no'])) {
            Response::abort(404);
        }
        return $unit;
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed>|null $existing Existing product row when updating
     * @return array<string,mixed>
     */
    private function payload(array $data, ?array $existing = null): array
    {
        $costPrice = Auth::can('costs.view')
            ? $this->request->float('cost_price')
            : (float) ($existing['cost_price'] ?? 0);
        $wholesalePrice = Auth::can('products.wholesale.view')
            ? $this->request->float('wholesale_price')
            : (float) ($existing['wholesale_price'] ?? 0);

        return [
            'category_id'     => $this->request->int('category_id') ?: null,
            'brand_id'        => $this->request->int('brand_id') ?: null,
            'unit_id'         => $this->request->int('unit_id') ?: null,
            'name'            => trim((string) $data['name']),
            'slug'            => slugify((string) $data['name']),
            'sku'             => trim((string) $data['sku']),
            'barcode'         => $this->request->string('barcode') ?: null,
            'type'            => $this->request->string('type', 'standard'),
            'description'     => $this->request->string('description') ?: null,
            'cost_price'      => $costPrice,
            'selling_price'   => $this->request->float('selling_price'),
            'wholesale_price' => $wholesalePrice,
            'tax_rate'        => 0, // Prices are VAT-inclusive; no separate tax % on products
            'alert_quantity'  => $this->request->float('alert_quantity', 5),
            'has_serial'      => $this->request->bool('has_serial') ? 1 : 0,
            'has_imei'        => $this->request->bool('has_imei') ? 1 : 0,
            'has_expiry'      => $this->request->bool('has_expiry') ? 1 : 0,
            'status'          => $this->request->bool('status') ? 1 : 0,
            'created_by'      => \App\Core\Auth::id(),
        ];
    }

    /**
     * @param array<string,mixed>|null $product
     * @return array<string,mixed>
     */
    private function formData(?array $product): array
    {
        return [
            'title'      => $product ? 'Edit Product' : 'New Product',
            'product'    => $product ? strip_product_wholesale(strip_product_costs($product)) : $product,
            'categories' => (new Category())->all(['status' => 1], 'name'),
            'brands'     => (new Brand())->all(['status' => 1], 'name'),
            'units'      => (new Unit())->all(['status' => 1], 'name'),
            'warehouses' => (new Warehouse())->all(['status' => 1], 'name'),
        ];
    }
}
