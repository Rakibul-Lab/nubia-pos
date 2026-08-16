<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

/**
 * Business reports with PDF / Excel / CSV export.
 *
 * @package App\Controllers
 */
final class ReportController extends Controller
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->authorize('reports.view');
        $this->view('reports.index', ['title' => 'Reports']);
    }

    /**
     * Resolve date range from period preset or custom dates.
     * Defaults to today.
     *
     * @return array{0:string,1:string,2:string} start, end, period
     */
    private function range(): array
    {
        $period = $this->request->string('period', 'today');
        $today  = date('Y-m-d');

        [$start, $end] = match ($period) {
            'yesterday' => [
                date('Y-m-d', strtotime('-1 day')),
                date('Y-m-d', strtotime('-1 day')),
            ],
            'week' => [
                date('Y-m-d', strtotime('monday this week')),
                $today,
            ],
            'month' => [
                date('Y-m-01'),
                $today,
            ],
            'year' => [
                date('Y-01-01'),
                $today,
            ],
            'custom' => [
                $this->request->string('start') ?: date('Y-m-01'),
                $this->request->string('end') ?: $today,
            ],
            default => [$today, $today], // today
        };

        if ($period !== 'custom' && !in_array($period, ['today', 'yesterday', 'week', 'month', 'year'], true)) {
            $period = 'today';
        }

        return [$start, $end, $period];
    }

    private function keyword(): string
    {
        return trim($this->request->string('q'));
    }

    /**
     * @param array<int,mixed> $params
     * @return array{0:string,1:array<int,mixed>}
     */
    private function withSearch(string $sql, array $params, string $searchSql, string $keyword): array
    {
        if ($keyword !== '') {
            $sql .= ' AND (' . $searchSql . ')';
            $like = '%' . $keyword . '%';
            $placeholders = substr_count($searchSql, '?');
            for ($i = 0; $i < $placeholders; $i++) {
                $params[] = $like;
            }
        }
        return [$sql, $params];
    }

    public function sales(): void
    {
        $this->authorize('reports.sales');
        [$start, $end, $period] = $this->range();
        $keyword = $this->keyword();
        $canProfit = Auth::can('profit.view');
        $canSalesTotal = Auth::can('sales_total.view');
        $canRevenue = Auth::can('revenue.view');
        $canDues = Auth::can('dues.view');
        $sql = 'SELECT s.invoice_no, s.sale_date, COALESCE(c.name, "Walk-in") AS customer,
                    s.payment_status'
                    . ($canSalesTotal ? ', s.total' : '')
                    . ($canRevenue ? ', s.paid' : '')
                    . ($canDues ? ', s.due' : '')
                    . ($canProfit ? ', s.profit' : '') . '
             FROM sales s LEFT JOIN customers c ON c.id = s.customer_id
             WHERE s.sale_date BETWEEN ? AND ? AND s.status != "cancelled"';
        [$sql, $params] = $this->withSearch(
            $sql,
            [$start, $end],
            's.invoice_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR s.payment_status LIKE ?',
            $keyword
        );
        $sql .= ' ORDER BY s.sale_date DESC, s.id DESC';
        $rows = $this->db->fetchAll($sql, $params);
        $summary = ['Orders' => count($rows)];
        $columns = ['invoice_no' => 'Invoice', 'sale_date' => 'Date', 'customer' => 'Customer'];
        $money = [];
        if ($canSalesTotal) {
            $summary['Total Sales'] = money(array_sum(array_column($rows, 'total')));
            $columns['total'] = 'Total';
            $money[] = 'total';
        }
        if ($canRevenue) {
            $summary['Total Revenue'] = money(array_sum(array_column($rows, 'paid')));
            $columns['paid'] = 'Paid';
            $money[] = 'paid';
        }
        if ($canDues) {
            $summary['Total Due'] = money(array_sum(array_column($rows, 'due')));
            $columns['due'] = 'Due';
            $money[] = 'due';
        }
        if ($canProfit) {
            $summary['Total Profit'] = money(array_sum(array_column($rows, 'profit')));
            $columns['profit'] = 'Profit';
            $money[] = 'profit';
        }
        $columns['payment_status'] = 'Status';
        $this->renderReport('Sales Report', 'sales', $rows,
            $columns,
            $summary, $start, $end, $period, $keyword,
            $money
        );
    }

    public function purchases(): void
    {
        $this->authorize('reports.purchases');
        [$start, $end, $period] = $this->range();
        $keyword = $this->keyword();
        $canCosts = Auth::can('costs.view');
        $sql = 'SELECT p.reference, p.purchase_date, COALESCE(s.name, "Cash") AS supplier'
            . ($canCosts ? ', p.total, p.paid, p.due' : '') . ', p.payment_status
             FROM purchases p LEFT JOIN suppliers s ON s.id = p.supplier_id
             WHERE p.purchase_date BETWEEN ? AND ? AND p.status != "cancelled"';
        [$sql, $params] = $this->withSearch(
            $sql,
            [$start, $end],
            'p.reference LIKE ? OR s.name LIKE ? OR s.phone LIKE ? OR p.payment_status LIKE ?',
            $keyword
        );
        $sql .= ' ORDER BY p.purchase_date DESC';
        $rows = $this->db->fetchAll($sql, $params);
        $columns = ['reference' => 'Reference', 'purchase_date' => 'Date', 'supplier' => 'Supplier'];
        $summary = ['Orders' => count($rows)];
        $moneyCols = [];
        if ($canCosts) {
            $summary = [
                'Total Purchase' => money(array_sum(array_column($rows, 'total'))),
                'Total Paid'     => money(array_sum(array_column($rows, 'paid'))),
                'Total Due'      => money(array_sum(array_column($rows, 'due'))),
            ];
            $columns['total'] = 'Total';
            $columns['paid'] = 'Paid';
            $columns['due'] = 'Due';
            $moneyCols = ['total', 'paid', 'due'];
        }
        $columns['payment_status'] = 'Status';
        $this->renderReport('Purchase Report', 'purchases', $rows,
            $columns,
            $summary, $start, $end, $period, $keyword, $moneyCols
        );
    }

    public function profit(): void
    {
        $this->authorize('reports.profit');
        [$start, $end, $period] = $this->range();
        $keyword = $this->keyword();
        $sql = 'SELECT sale_date AS date, COUNT(*) AS orders, SUM(total) AS revenue, SUM(total_cost) AS cost, SUM(profit) AS profit
             FROM sales WHERE sale_date BETWEEN ? AND ? AND status != "cancelled"';
        [$sql, $params] = $this->withSearch($sql, [$start, $end], 'sale_date LIKE ? OR invoice_no LIKE ?', $keyword);
        $sql .= ' GROUP BY sale_date ORDER BY sale_date DESC';
        $rows = $this->db->fetchAll($sql, $params);
        $expenses = (float) $this->db->scalar('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?', [$start, $end]);
        $grossProfit = array_sum(array_column($rows, 'profit'));
        $summary = ['Orders' => array_sum(array_column($rows, 'orders'))];
        $columns = ['date' => 'Date', 'orders' => 'Orders'];
        $moneyCols = [];
        if (Auth::can('sales_total.view')) {
            $summary['Sales Total'] = money(array_sum(array_column($rows, 'revenue')));
            $columns['revenue'] = 'Sales Total';
            $moneyCols[] = 'revenue';
        }
        if (Auth::can('costs.view')) {
            $columns['cost'] = 'Cost';
            $moneyCols[] = 'cost';
        }
        if (Auth::can('profit.view')) {
            $summary['Gross Profit'] = money($grossProfit);
            $summary['Net Profit'] = money($grossProfit - $expenses);
            $columns['profit'] = 'Profit';
            $moneyCols[] = 'profit';
        }
        if (Auth::can('expenses.view')) {
            $summary['Expenses'] = money($expenses);
        }
        $this->renderReport('Profit & Loss Report', 'profit', $rows,
            $columns,
            $summary, $start, $end, $period, $keyword, $moneyCols
        );
    }

    public function expenses(): void
    {
        $this->authorize('reports.expenses');
        [$start, $end, $period] = $this->range();
        $keyword = $this->keyword();
        $sql = 'SELECT e.reference, e.expense_date, e.title, COALESCE(ec.name, "-") AS category, e.method, e.amount
             FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id
             WHERE e.expense_date BETWEEN ? AND ?';
        [$sql, $params] = $this->withSearch(
            $sql,
            [$start, $end],
            'e.reference LIKE ? OR e.title LIKE ? OR ec.name LIKE ? OR e.method LIKE ?',
            $keyword
        );
        $sql .= ' ORDER BY e.expense_date DESC';
        $rows = $this->db->fetchAll($sql, $params);
        $summary = ['Total Expenses' => money(array_sum(array_column($rows, 'amount')))];
        $this->renderReport('Expense Report', 'expenses', $rows,
            ['reference' => 'Reference', 'expense_date' => 'Date', 'title' => 'Title', 'category' => 'Category', 'method' => 'Method', 'amount' => 'Amount'],
            $summary, $start, $end, $period, $keyword, ['amount']
        );
    }

    public function stock(): void
    {
        $this->authorize('reports.stock');
        $keyword = $this->keyword();
        $canCosts = Auth::can('costs.view');
        $canStockValue = Auth::can('stock_value.view');
        $sql = 'SELECT p.name, p.sku, p.alert_quantity, p.selling_price'
            . ($canCosts ? ', p.cost_price' : '')
            . ($canStockValue ? ', COALESCE(SUM(s.quantity),0) * p.cost_price AS stock_value' : '') . ',
                    COALESCE(SUM(s.quantity),0) AS stock
             FROM products p LEFT JOIN stock s ON s.product_id = p.id
             WHERE p.status = 1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
            $like = '%' . $keyword . '%';
            $params = [$like, $like, $like];
        }
        $sql .= ' GROUP BY p.id, p.name, p.sku, p.alert_quantity, p.selling_price'
            . ($canCosts || $canStockValue ? ', p.cost_price' : '') . ' ORDER BY stock ASC';
        $rows = $this->db->fetchAll($sql, $params);
        $summary = [
            'Products'  => count($rows),
            'Low Stock' => count(array_filter($rows, static fn ($r) => (float) $r['stock'] <= (float) $r['alert_quantity'])),
        ];
        $columns = ['name' => 'Product', 'sku' => 'SKU', 'stock' => 'In Stock', 'alert_quantity' => 'Alert Qty'];
        $moneyCols = [];
        if ($canCosts) {
            $columns['cost_price'] = 'Cost';
            $moneyCols[] = 'cost_price';
        }
        if ($canStockValue) {
            $summary['Stock Value'] = money(array_sum(array_column($rows, 'stock_value')));
            $columns['stock_value'] = 'Stock Value';
            $moneyCols[] = 'stock_value';
        }
        $this->renderReport('Stock Report', 'stock', $rows,
            $columns,
            $summary, null, null, 'today', $keyword, $moneyCols
        );
    }

    public function customers(): void
    {
        $this->authorize('reports.customers');
        $keyword = $this->keyword();
        $canSalesTotal = Auth::can('sales_total.view');
        $canDues = Auth::can('dues.view');
        $sql = 'SELECT c.name, c.phone, c.type, COUNT(s.id) AS orders'
            . ($canSalesTotal ? ', COALESCE(SUM(s.total),0) AS total' : '')
            . ($canDues ? ', COALESCE(SUM(s.due),0) AS due' : '') . '
             FROM customers c LEFT JOIN sales s ON s.customer_id = c.id AND s.status != "cancelled"
             WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ? OR c.type LIKE ?)';
            $like = '%' . $keyword . '%';
            $params = [$like, $like, $like, $like];
        }
        $sql .= ' GROUP BY c.id, c.name, c.phone, c.type ORDER BY orders DESC';
        $rows = $this->db->fetchAll($sql, $params);
        $summary = ['Customers' => count($rows)];
        $columns = ['name' => 'Customer', 'phone' => 'Phone', 'type' => 'Type', 'orders' => 'Orders'];
        $moneyCols = [];
        if ($canSalesTotal) {
            $summary['Total Sales'] = money(array_sum(array_column($rows, 'total')));
            $columns['total'] = 'Total Spent';
            $moneyCols[] = 'total';
        }
        if ($canDues) {
            $summary['Total Due'] = money(array_sum(array_column($rows, 'due')));
            $columns['due'] = 'Due';
            $moneyCols[] = 'due';
        }
        $this->renderReport('Customer Report', 'customers', $rows,
            $columns,
            $summary, null, null, 'today', $keyword, $moneyCols
        );
    }

    public function suppliers(): void
    {
        $this->authorize('reports.suppliers');
        $keyword = $this->keyword();
        $canCosts = Auth::can('costs.view');
        $sql = 'SELECT s.name, s.phone, s.company, COUNT(p.id) AS orders'
            . ($canCosts ? ', COALESCE(SUM(p.total),0) AS total, COALESCE(SUM(p.due),0) AS due' : '') . '
             FROM suppliers s LEFT JOIN purchases p ON p.supplier_id = s.id AND p.status != "cancelled"
             WHERE 1=1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (s.name LIKE ? OR s.phone LIKE ? OR s.company LIKE ? OR s.email LIKE ?)';
            $like = '%' . $keyword . '%';
            $params = [$like, $like, $like, $like];
        }
        $sql .= ' GROUP BY s.id, s.name, s.phone, s.company ORDER BY orders DESC';
        $rows = $this->db->fetchAll($sql, $params);
        $columns = ['name' => 'Supplier', 'phone' => 'Phone', 'company' => 'Company', 'orders' => 'Orders'];
        $summary = ['Suppliers' => count($rows)];
        $moneyCols = [];
        if ($canCosts) {
            $summary['Total Purchase'] = money(array_sum(array_column($rows, 'total')));
            $summary['Total Payable'] = money(array_sum(array_column($rows, 'due')));
            $columns['total'] = 'Total Purchase';
            $columns['due'] = 'Payable';
            $moneyCols = ['total', 'due'];
        }
        $this->renderReport('Supplier Report', 'suppliers', $rows,
            $columns,
            $summary, null, null, 'today', $keyword, $moneyCols
        );
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,string> $columns
     * @param array<string,mixed> $summary
     * @param string[] $money
     */
    private function renderReport(
        string $title,
        string $type,
        array $rows,
        array $columns,
        array $summary,
        ?string $start,
        ?string $end,
        string $period = 'today',
        string $keyword = '',
        array $money = []
    ): void {
        $hasDate = $start !== null;

        if ($this->request->wantsJson()) {
            $this->json([
                'success'  => true,
                'title'    => $title,
                'type'     => $type,
                'rows'     => $rows,
                'columns'  => $columns,
                'summary'  => $summary,
                'money'    => $money,
                'period'   => $period,
                'keyword'  => $keyword,
                'start'    => $start,
                'end'      => $end,
                'has_date' => $hasDate,
                'count'    => count($rows),
            ]);
        }

        $this->view('reports.show', compact(
            'title', 'type', 'rows', 'columns', 'summary', 'start', 'end', 'money', 'period', 'keyword', 'hasDate'
        ));
    }

    public function export(string $type): void
    {
        $this->authorize('reports.export');
        $typePermissions = [
            'sales'     => 'reports.sales',
            'purchases' => 'reports.purchases',
            'profit'    => 'reports.profit',
            'expenses'  => 'reports.expenses',
            'stock'     => 'reports.stock',
            'customers' => 'reports.customers',
            'suppliers' => 'reports.suppliers',
        ];
        if (!isset($typePermissions[$type])) {
            Response::abort(404);
        }
        $this->authorize($typePermissions[$type]);
        $format = $this->request->string('format', 'csv');

        // Re-run the report query to obtain rows for the given type.
        [$rows, $columns, $title] = $this->dataFor($type);

        match ($format) {
            'excel' => $this->exportExcel($title, $rows, $columns),
            'pdf'   => $this->exportPdf($title, $rows, $columns),
            default => $this->exportCsv($title, $rows, $columns),
        };
    }

    /**
     * @return array{0:array<int,array<string,mixed>>,1:array<string,string>,2:string}
     */
    private function dataFor(string $type): array
    {
        [$start, $end] = $this->range();
        $keyword = $this->keyword();

        return match ($type) {
            'purchases' => (function () use ($start, $end, $keyword) {
                $canCosts = Auth::can('costs.view');
                $sql = 'SELECT p.reference, p.purchase_date, COALESCE(s.name,"Cash") supplier'
                    . ($canCosts ? ', p.total, p.paid, p.due' : '') . '
                        FROM purchases p LEFT JOIN suppliers s ON s.id=p.supplier_id
                        WHERE p.purchase_date BETWEEN ? AND ? AND p.status != "cancelled"';
                [$sql, $params] = $this->withSearch($sql, [$start, $end], 'p.reference LIKE ? OR s.name LIKE ?', $keyword);
                $columns = ['reference' => 'Reference', 'purchase_date' => 'Date', 'supplier' => 'Supplier'];
                if ($canCosts) {
                    $columns['total'] = 'Total';
                    $columns['paid'] = 'Paid';
                    $columns['due'] = 'Due';
                }
                return [
                    $this->db->fetchAll($sql, $params),
                    $columns,
                    'Purchase Report',
                ];
            })(),
            'stock' => (function () use ($keyword) {
                $canCosts = Auth::can('costs.view');
                $canStockValue = Auth::can('stock_value.view');
                $sql = 'SELECT p.name, p.sku, COALESCE(SUM(s.quantity),0) stock, p.selling_price'
                    . ($canCosts ? ', p.cost_price' : '')
                    . ($canStockValue ? ', COALESCE(SUM(s.quantity),0) * p.cost_price AS stock_value' : '') . '
                        FROM products p LEFT JOIN stock s ON s.product_id=p.id WHERE p.status=1';
                $params = [];
                if ($keyword !== '') {
                    $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
                    $like = '%' . $keyword . '%';
                    $params = [$like, $like];
                }
                $sql .= ' GROUP BY p.id, p.name, p.sku, p.selling_price'
                    . ($canCosts || $canStockValue ? ', p.cost_price' : '');
                $columns = ['name' => 'Product', 'sku' => 'SKU', 'stock' => 'Stock'];
                if ($canCosts) {
                    $columns['cost_price'] = 'Cost';
                }
                if ($canStockValue) {
                    $columns['stock_value'] = 'Stock Value';
                }
                $columns['selling_price'] = 'Price';
                return [
                    $this->db->fetchAll($sql, $params),
                    $columns,
                    'Stock Report',
                ];
            })(),
            'expenses' => (function () use ($start, $end, $keyword) {
                $sql = 'SELECT reference, expense_date, title, amount, method FROM expenses WHERE expense_date BETWEEN ? AND ?';
                [$sql, $params] = $this->withSearch($sql, [$start, $end], 'reference LIKE ? OR title LIKE ? OR method LIKE ?', $keyword);
                return [
                    $this->db->fetchAll($sql, $params),
                    ['reference' => 'Reference', 'expense_date' => 'Date', 'title' => 'Title', 'amount' => 'Amount', 'method' => 'Method'],
                    'Expense Report',
                ];
            })(),
            'profit' => (function () use ($start, $end) {
                $columns = ['date' => 'Date', 'orders' => 'Orders'];
                if (Auth::can('sales_total.view')) {
                    $columns['revenue'] = 'Sales Total';
                }
                if (Auth::can('costs.view')) {
                    $columns['cost'] = 'Cost';
                }
                if (Auth::can('profit.view')) {
                    $columns['profit'] = 'Profit';
                }
                return [
                    $this->db->fetchAll(
                        'SELECT sale_date AS date, COUNT(*) AS orders, SUM(total) AS revenue, SUM(total_cost) AS cost, SUM(profit) AS profit
                         FROM sales WHERE sale_date BETWEEN ? AND ? AND status != "cancelled"
                         GROUP BY sale_date ORDER BY sale_date DESC',
                        [$start, $end]
                    ),
                    $columns,
                    'Profit & Loss Report',
                ];
            })(),
            'customers' => (function () use ($keyword) {
                $canSalesTotal = Auth::can('sales_total.view');
                $canDues = Auth::can('dues.view');
                $sql = 'SELECT c.name, c.phone, c.type, COUNT(s.id) AS orders'
                    . ($canSalesTotal ? ', COALESCE(SUM(s.total),0) AS total' : '')
                    . ($canDues ? ', COALESCE(SUM(s.due),0) AS due' : '') . '
                        FROM customers c LEFT JOIN sales s ON s.customer_id=c.id AND s.status!="cancelled" WHERE 1=1';
                $params = [];
                if ($keyword !== '') {
                    $sql .= ' AND (c.name LIKE ? OR c.phone LIKE ?)';
                    $like = '%' . $keyword . '%';
                    $params = [$like, $like];
                }
                $sql .= ' GROUP BY c.id, c.name, c.phone, c.type ORDER BY orders DESC';
                $columns = ['name' => 'Customer', 'phone' => 'Phone', 'type' => 'Type', 'orders' => 'Orders'];
                if ($canSalesTotal) {
                    $columns['total'] = 'Total Spent';
                }
                if ($canDues) {
                    $columns['due'] = 'Due';
                }
                return [
                    $this->db->fetchAll($sql, $params),
                    $columns,
                    'Customer Report',
                ];
            })(),
            'suppliers' => (function () use ($keyword) {
                $canCosts = Auth::can('costs.view');
                $sql = 'SELECT s.name, s.phone, s.company, COUNT(p.id) AS orders'
                    . ($canCosts ? ', COALESCE(SUM(p.total),0) AS total, COALESCE(SUM(p.due),0) AS due' : '') . '
                        FROM suppliers s LEFT JOIN purchases p ON p.supplier_id=s.id AND p.status!="cancelled" WHERE 1=1';
                $params = [];
                if ($keyword !== '') {
                    $sql .= ' AND (s.name LIKE ? OR s.phone LIKE ? OR s.company LIKE ?)';
                    $like = '%' . $keyword . '%';
                    $params = [$like, $like, $like];
                }
                $sql .= ' GROUP BY s.id, s.name, s.phone, s.company ORDER BY orders DESC';
                $columns = ['name' => 'Supplier', 'phone' => 'Phone', 'company' => 'Company', 'orders' => 'Orders'];
                if ($canCosts) {
                    $columns['total'] = 'Total Purchase';
                    $columns['due'] = 'Payable';
                }
                return [
                    $this->db->fetchAll($sql, $params),
                    $columns,
                    'Supplier Report',
                ];
            })(),
            default => (function () use ($start, $end, $keyword) {
                $canProfit = Auth::can('profit.view');
                $canSalesTotal = Auth::can('sales_total.view');
                $canRevenue = Auth::can('revenue.view');
                $canDues = Auth::can('dues.view');
                $sql = 'SELECT s.invoice_no, s.sale_date, COALESCE(c.name,"Walk-in") customer'
                    . ($canSalesTotal ? ', s.total' : '')
                    . ($canRevenue ? ', s.paid' : '')
                    . ($canDues ? ', s.due' : '')
                    . ($canProfit ? ', s.profit' : '') . '
                        FROM sales s LEFT JOIN customers c ON c.id=s.customer_id
                        WHERE s.sale_date BETWEEN ? AND ? AND s.status!="cancelled"';
                [$sql, $params] = $this->withSearch($sql, [$start, $end], 's.invoice_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ?', $keyword);
                $columns = ['invoice_no' => 'Invoice', 'sale_date' => 'Date', 'customer' => 'Customer'];
                if ($canSalesTotal) {
                    $columns['total'] = 'Total';
                }
                if ($canRevenue) {
                    $columns['paid'] = 'Paid';
                }
                if ($canDues) {
                    $columns['due'] = 'Due';
                }
                if ($canProfit) {
                    $columns['profit'] = 'Profit';
                }
                return [
                    $this->db->fetchAll($sql, $params),
                    $columns,
                    'Sales Report',
                ];
            })(),
        };
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,string> $columns
     */
    private function exportCsv(string $title, array $rows, array $columns): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . slugify($title) . '-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array_values($columns));
        foreach ($rows as $row) {
            $line = [];
            foreach (array_keys($columns) as $key) {
                $line[] = $row[$key] ?? '';
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,string> $columns
     */
    private function exportExcel(string $title, array $rows, array $columns): never
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
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
        $html = '<h2 style="font-family:Arial;">' . e($title) . '</h2><table width="100%" style="border-collapse:collapse;font-family:Arial;font-size:11px;"><thead><tr>';
        foreach ($columns as $label) {
            $html .= '<th style="border:1px solid #ccc;padding:6px;background:#f1f5f9;text-align:left;">' . e($label) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (array_keys($columns) as $key) {
                $html .= '<td style="border:1px solid #eee;padding:6px;">' . e((string) ($row[$key] ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(slugify($title) . '-' . date('Ymd') . '.pdf', ['Attachment' => true]);
        exit;
    }
}
