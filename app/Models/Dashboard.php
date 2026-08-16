<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Aggregated read-model powering the dashboard KPIs and charts.
 *
 * @package App\Models
 */
final class Dashboard
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function scalar(string $sql, array $params = []): float
    {
        return (float) $this->db->scalar($sql, $params);
    }

    /**
     * Period KPIs plus always-on snapshot metrics.
     *
     * @return array<string,float|int>
     */
    public function kpis(string $start, string $end): array
    {
        return [
            'sales'        => $this->scalar(
                "SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$start, $end]
            ),
            'purchase'     => $this->scalar(
                "SELECT COALESCE(SUM(total),0) FROM purchases WHERE purchase_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$start, $end]
            ),
            'profit'       => $this->scalar(
                "SELECT COALESCE(SUM(profit),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$start, $end]
            ) + $this->scalar(
                'SELECT COALESCE(SUM(profit),0) FROM direct_transactions WHERE DATE(created_at) BETWEEN ? AND ?',
                [$start, $end]
            ),
            'expense'      => $this->scalar(
                'SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?',
                [$start, $end]
            ),
            'due_collect'  => $this->scalar(
                "SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at BETWEEN ? AND ? AND direction='in' AND payable_type IN ('sale','customer')",
                [$start, $end]
            ),
            'due_pay'      => $this->scalar(
                "SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at BETWEEN ? AND ? AND direction='out' AND payable_type IN ('purchase','supplier')",
                [$start, $end]
            ),
            'revenue'      => $this->scalar(
                "SELECT COALESCE(SUM(paid),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$start, $end]
            ),

            'stock_value'     => $this->scalar('SELECT COALESCE(SUM(s.quantity * p.cost_price),0) FROM stock s JOIN products p ON p.id = s.product_id'),
            'total_products'  => $this->scalar('SELECT COUNT(*) FROM products WHERE status = 1'),
            'total_customers' => $this->scalar('SELECT COUNT(*) FROM customers WHERE status = 1'),
            'total_suppliers' => $this->scalar('SELECT COUNT(*) FROM suppliers WHERE status = 1'),
            'receivable'      => $this->scalar("SELECT COALESCE(SUM(due),0) FROM sales WHERE status != 'cancelled'"),
            'payable'         => $this->scalar("SELECT COALESCE(SUM(due),0) FROM purchases WHERE status != 'cancelled'"),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function topProducts(string $start, string $end, int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT p.id, p.name, p.sku, SUM(si.quantity) AS qty, SUM(si.subtotal) AS revenue
             FROM sale_items si
             JOIN products p ON p.id = si.product_id
             JOIN sales s ON s.id = si.sale_id AND s.status != "cancelled"
             WHERE s.sale_date BETWEEN ? AND ?
             GROUP BY p.id, p.name, p.sku
             ORDER BY qty DESC
             LIMIT ' . (int) $limit,
            [$start, $end]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recentSales(string $start, string $end, int $limit = 8): array
    {
        return $this->db->fetchAll(
            'SELECT s.id, s.invoice_no, s.total, s.payment_status, s.sale_date, s.created_at,
                    COALESCE(c.name, "Walk-in Customer") AS customer
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             WHERE s.sale_date BETWEEN ? AND ? AND s.status != "cancelled"
             ORDER BY s.id DESC
             LIMIT ' . (int) $limit,
            [$start, $end]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function lowStock(int $limit = 6): array
    {
        return $this->db->fetchAll(
            'SELECT p.id, p.name, p.sku, p.alert_quantity, COALESCE(SUM(s.quantity),0) AS qty
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id
             WHERE p.status = 1
             GROUP BY p.id, p.name, p.sku, p.alert_quantity
             HAVING qty <= p.alert_quantity
             ORDER BY qty ASC
             LIMIT ' . (int) $limit
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function bestCustomers(string $start, string $end, int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT c.id, c.name, SUM(s.total) AS spent
             FROM sales s JOIN customers c ON c.id = s.customer_id
             WHERE s.status != "cancelled" AND s.sale_date BETWEEN ? AND ?
             GROUP BY c.id, c.name ORDER BY spent DESC LIMIT ' . (int) $limit,
            [$start, $end]
        );
    }

    /**
     * Sales vs purchase vs profit for a date range (chart data).
     *
     * @return array<string,array<int,mixed>>
     */
    public function salesTrend(string $start, string $end): array
    {
        $startTs = strtotime($start);
        $endTs   = strtotime($end);
        if ($startTs === false || $endTs === false || $endTs < $startTs) {
            return ['labels' => [], 'sales' => [], 'profit' => [], 'purchases' => []];
        }

        $days = (int) (($endTs - $startTs) / 86400) + 1;

        // Long ranges: monthly buckets keep the chart readable.
        if ($days > 62) {
            return $this->salesTrendMonthly($start, $end);
        }

        $labels = [];
        $sales  = [];
        $profit = [];
        $purch  = [];

        for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
            $date     = date('Y-m-d', $ts);
            $labels[] = date('M j', $ts);
            $sales[]  = $this->scalar("SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date = ? AND status != 'cancelled'", [$date]);
            $profit[] = $this->scalar("SELECT COALESCE(SUM(profit),0) FROM sales WHERE sale_date = ? AND status != 'cancelled'", [$date]);
            $purch[]  = $this->scalar("SELECT COALESCE(SUM(total),0) FROM purchases WHERE purchase_date = ? AND status != 'cancelled'", [$date]);
        }

        return ['labels' => $labels, 'sales' => $sales, 'profit' => $profit, 'purchases' => $purch];
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    private function salesTrendMonthly(string $start, string $end): array
    {
        $labels = [];
        $sales  = [];
        $profit = [];
        $purch  = [];

        $cursor = date('Y-m-01', strtotime($start));
        $last   = date('Y-m-01', strtotime($end));

        while ($cursor <= $last) {
            $monthStart = $cursor;
            $monthEnd   = date('Y-m-t', strtotime($cursor));
            $from       = max($start, $monthStart);
            $to         = min($end, $monthEnd);

            $labels[] = date('M Y', strtotime($cursor));
            $sales[]  = $this->scalar(
                "SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$from, $to]
            );
            $profit[] = $this->scalar(
                "SELECT COALESCE(SUM(profit),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$from, $to]
            );
            $purch[] = $this->scalar(
                "SELECT COALESCE(SUM(total),0) FROM purchases WHERE purchase_date BETWEEN ? AND ? AND status != 'cancelled'",
                [$from, $to]
            );

            $cursor = date('Y-m-01', strtotime($cursor . ' +1 month'));
        }

        return ['labels' => $labels, 'sales' => $sales, 'profit' => $profit, 'purchases' => $purch];
    }

    /**
     * Payment method distribution for the selected range.
     *
     * @return array<string,mixed>
     */
    public function paymentBreakdown(string $start, string $end): array
    {
        $rows = $this->db->fetchAll(
            "SELECT payment_method AS method, COALESCE(SUM(total),0) AS amount
             FROM sales WHERE sale_date BETWEEN ? AND ? AND status != 'cancelled'
             GROUP BY payment_method",
            [$start, $end]
        );
        return [
            'labels' => array_map(static fn ($r) => ucfirst((string) $r['method']), $rows),
            'data'   => array_map(static fn ($r) => (float) $r['amount'], $rows),
        ];
    }
}
