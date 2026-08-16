<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Supplier;
use App\Services\PaymentService;

/**
 * Supplier CRUD, ledger and payments.
 *
 * @package App\Controllers
 */
final class SupplierController extends Controller
{
    private Supplier $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Supplier();
    }

    public function index(): void
    {
        $this->authorize('suppliers.view');
        $keyword = $this->request->string('q');
        $result  = $this->model->search($keyword, $this->request->int('page', 1));
        $this->view('suppliers.index', [
            'title'     => 'Suppliers',
            'suppliers' => $result['data'],
            'meta'      => $result,
            'keyword'   => $keyword,
        ]);
    }

    public function create(): void
    {
        $this->authorize('suppliers.create');
        $this->view('suppliers.form', ['title' => 'New Supplier', 'supplier' => null]);
    }

    public function edit(string $id): void
    {
        $this->authorize('suppliers.edit');
        $supplier = $this->model->find((int) $id);
        if (!$supplier) {
            Response::abort(404);
        }
        $this->view('suppliers.form', ['title' => 'Edit Supplier', 'supplier' => $supplier]);
    }

    public function show(string $id): void
    {
        $this->authorize('suppliers.view');
        $supplier = $this->model->find((int) $id);
        if (!$supplier) {
            Response::abort(404);
        }
        $this->view('suppliers.show', [
            'title'     => $supplier['name'],
            'supplier'  => $supplier,
            'ledger'    => $this->model->ledger((int) $id),
            'balance'   => $this->model->balance((int) $id),
            'purchases' => Auth::can('purchases.view')
                ? Database::getInstance()->fetchAll(
                    'SELECT * FROM purchases WHERE supplier_id = ? ORDER BY id DESC LIMIT 20',
                    [(int) $id]
                )
                : [],
        ]);
    }

    public function store(): void
    {
        $this->authorize('suppliers.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:2|max:160']);
        $id   = $this->model->create($this->payload($data));
        ActivityLog::record('supplier.created', 'Suppliers', 'Created supplier ' . $data['name'], 'supplier', (int) $id);
        flash('success', 'Supplier created.');
        redirect('suppliers/' . $id);
    }

    public function update(string $id): void
    {
        $this->authorize('suppliers.edit');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:2|max:160']);
        $this->model->update((int) $id, $this->payload($data));
        flash('success', 'Supplier updated.');
        redirect('suppliers/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->authorize('suppliers.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Supplier deleted.');
        redirect('suppliers');
    }

    public function makePayment(string $id): void
    {
        $this->authorize('suppliers.payment');
        $this->verifyCsrf();
        $amount = $this->request->float('amount');
        if ($amount <= 0) {
            flash('error', 'Enter a valid amount.');
            redirect('suppliers/' . $id);
        }

        Database::getInstance()->transaction(function () use ($id, $amount): void {
            $due = Database::getInstance()->fetchAll(
                'SELECT id, due FROM purchases WHERE supplier_id = ? AND due > 0 AND status != "cancelled" ORDER BY id ASC',
                [(int) $id]
            );
            $remaining = $amount;
            foreach ($due as $purchase) {
                if ($remaining <= 0) {
                    break;
                }
                $applied = min($remaining, (float) $purchase['due']);
                Database::getInstance()->query(
                    'UPDATE purchases SET paid = paid + ?, due = due - ?,
                     payment_status = CASE WHEN due - ? <= 0 THEN "paid" ELSE "partial" END
                     WHERE id = ?',
                    [$applied, $applied, $applied, $purchase['id']]
                );
                $remaining -= $applied;
            }
            PaymentService::record(
                'supplier', (int) $id, 'out', $amount,
                $this->request->string('method', 'cash'), 'supplier', (int) $id,
                'Supplier payment'
            );
        });

        ActivityLog::record('supplier.payment', 'Suppliers', 'Paid ' . money($amount) . ' to supplier #' . $id);
        flash('success', 'Payment of ' . money($amount) . ' recorded.');
        redirect('suppliers/' . $id);
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function payload(array $data): array
    {
        return [
            'name'            => trim((string) $data['name']),
            'phone'           => $this->request->string('phone') ?: null,
            'email'           => $this->request->string('email') ?: null,
            'company'         => $this->request->string('company') ?: null,
            'address'         => $this->request->string('address') ?: null,
            'city'            => $this->request->string('city') ?: null,
            'opening_balance' => $this->request->float('opening_balance'),
            'status'          => 1,
        ];
    }
}
