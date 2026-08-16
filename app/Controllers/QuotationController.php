<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\Customer;

/**
 * Quotations / estimates.
 *
 * @package App\Controllers
 */
final class QuotationController extends Controller
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->authorize('quotations.view');
        $quotations = $this->db->fetchAll(
            'SELECT q.*, COALESCE(c.name, "-") AS customer_name
             FROM quotations q LEFT JOIN customers c ON c.id = q.customer_id
             ORDER BY q.id DESC LIMIT 100'
        );
        $this->view('quotations.index', ['title' => 'Quotations', 'quotations' => $quotations]);
    }

    public function create(): void
    {
        $this->authorize('quotations.create');
        $this->view('quotations.form', [
            'title'     => 'New Quotation',
            'customers' => (new Customer())->all(['status' => 1], 'name'),
        ]);
    }

    public function store(): void
    {
        $this->authorize('quotations.create');
        $this->verifyCsrf();

        $productIds = (array) $this->request->input('product_id', []);
        $quantities = (array) $this->request->input('quantity', []);
        $prices     = (array) $this->request->input('unit_price', []);

        $subtotal = 0.0;
        $items    = [];
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (float) ($quantities[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $line = round($qty * $price, 2);
            $subtotal += $line;
            $items[] = ['product_id' => $pid, 'quantity' => $qty, 'unit_price' => $price, 'subtotal' => $line];
        }
        if ($items === []) {
            flash('error', 'Add at least one product.');
            redirect('quotations/create');
        }

        $discount = $this->request->float('discount');
        $tax      = $this->request->float('tax');
        $total    = max(0, $subtotal - $discount + $tax);

        $id = $this->db->transaction(function () use ($items, $subtotal, $discount, $tax, $total) {
            $qid = $this->db->insert('quotations', [
                'reference'   => generate_code('QT'),
                'customer_id' => $this->request->int('customer_id') ?: null,
                'quote_date'  => date('Y-m-d'),
                'valid_until' => $this->request->string('valid_until') ?: null,
                'subtotal'    => $subtotal,
                'discount'    => $discount,
                'tax'         => $tax,
                'total'       => $total,
                'status'      => 'sent',
                'note'        => $this->request->string('note') ?: null,
                'created_by'  => Auth::id(),
            ]);
            foreach ($items as $it) {
                $this->db->insert('quotation_items', [
                    'quotation_id' => $qid,
                    'product_id'   => $it['product_id'],
                    'quantity'     => $it['quantity'],
                    'unit_price'   => $it['unit_price'],
                    'subtotal'     => $it['subtotal'],
                ]);
            }
            return $qid;
        });

        flash('success', 'Quotation created.');
        redirect('quotations/' . $id);
    }

    public function show(string $id): void
    {
        $this->authorize('quotations.view');
        $quote = $this->db->fetch(
            'SELECT q.*, c.name AS customer_name, c.phone AS customer_phone FROM quotations q LEFT JOIN customers c ON c.id = q.customer_id WHERE q.id = ?',
            [(int) $id]
        );
        if (!$quote) {
            Response::abort(404);
        }
        $items = $this->db->fetchAll(
            'SELECT qi.*, p.name AS product_name, p.sku FROM quotation_items qi JOIN products p ON p.id = qi.product_id WHERE qi.quotation_id = ?',
            [(int) $id]
        );
        $this->view('quotations.show', ['title' => $quote['reference'], 'quote' => $quote, 'items' => $items]);
    }
}
