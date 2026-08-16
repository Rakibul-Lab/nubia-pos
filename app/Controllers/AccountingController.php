<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * Accounting: cash/bank book, P&L, payables & receivables.
 *
 * @package App\Controllers
 */
final class AccountingController extends Controller
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function cashBook(): void
    {
        $this->authorize('accounting.cash_book');
        $this->book('cash', 'Cash Book');
    }

    public function bankBook(): void
    {
        $this->authorize('accounting.bank_book');
        $this->book('bank', 'Bank Book');
    }

    private function book(string $method, string $title): void
    {
        $start = $this->request->string('start') ?: date('Y-m-01');
        $end   = $this->request->string('end') ?: date('Y-m-d');

        $rows = $this->db->fetchAll(
            'SELECT paid_at AS date, reference, payable_type, direction, amount, note
             FROM payments WHERE method = ? AND paid_at BETWEEN ? AND ?
             ORDER BY paid_at, id',
            [$method, $start, $end]
        );

        $in  = array_sum(array_map(static fn ($r) => $r['direction'] === 'in' ? (float) $r['amount'] : 0, $rows));
        $out = array_sum(array_map(static fn ($r) => $r['direction'] === 'out' ? (float) $r['amount'] : 0, $rows));

        $this->view('accounting.book', [
            'title'   => $title,
            'rows'    => $rows,
            'in'      => $in,
            'out'     => $out,
            'balance' => $in - $out,
            'start'   => $start,
            'end'     => $end,
            'method'  => $method,
        ]);
    }

    public function profitLoss(): void
    {
        $this->authorize('accounting.profit_loss');
        $start = $this->request->string('start') ?: date('Y-m-01');
        $end   = $this->request->string('end') ?: date('Y-m-d');

        $revenue     = (float) $this->db->scalar('SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != "cancelled"', [$start, $end]);
        $cogs        = (float) $this->db->scalar('SELECT COALESCE(SUM(total_cost),0) FROM sales WHERE sale_date BETWEEN ? AND ? AND status != "cancelled"', [$start, $end]);
        $expenses    = (float) $this->db->scalar('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?', [$start, $end]);
        $directProfit = (float) $this->db->scalar('SELECT COALESCE(SUM(profit),0) FROM direct_transactions WHERE DATE(created_at) BETWEEN ? AND ?', [$start, $end]);

        $this->view('accounting.pnl', [
            'title'        => 'Profit & Loss',
            'revenue'      => $revenue,
            'cogs'         => $cogs,
            'grossProfit'  => $revenue - $cogs,
            'expenses'     => $expenses,
            'directProfit' => $directProfit,
            'netProfit'    => ($revenue - $cogs) - $expenses + $directProfit,
            'start'        => $start,
            'end'          => $end,
        ]);
    }

    public function payables(): void
    {
        $this->authorize('accounting.payables');
        $rows = $this->db->fetchAll(
            'SELECT s.id, s.name, s.phone, COALESCE(SUM(p.due),0) + s.opening_balance AS payable
             FROM suppliers s LEFT JOIN purchases p ON p.supplier_id = s.id AND p.status != "cancelled"
             GROUP BY s.id, s.name, s.phone, s.opening_balance HAVING payable > 0 ORDER BY payable DESC'
        );
        $this->view('accounting.parties', [
            'title'    => 'Accounts Payable',
            'rows'     => $rows,
            'total'    => array_sum(array_column($rows, 'payable')),
            'amountKey' => 'payable',
            'linkBase' => 'suppliers',
            'label'    => 'Payable',
        ]);
    }

    public function receivables(): void
    {
        $this->authorize('accounting.receivables');
        $rows = $this->db->fetchAll(
            'SELECT c.id, c.name, c.phone, COALESCE(SUM(s.due),0) + c.opening_balance AS receivable
             FROM customers c LEFT JOIN sales s ON s.customer_id = c.id AND s.status != "cancelled"
             GROUP BY c.id, c.name, c.phone, c.opening_balance HAVING receivable > 0 ORDER BY receivable DESC'
        );
        $this->view('accounting.parties', [
            'title'    => 'Accounts Receivable',
            'rows'     => $rows,
            'total'    => array_sum(array_column($rows, 'receivable')),
            'amountKey' => 'receivable',
            'linkBase' => 'customers',
            'label'    => 'Receivable',
        ]);
    }
}
