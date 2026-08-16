<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Services\PaymentService;

/**
 * Customer CRUD, ledger and due collection.
 *
 * @package App\Controllers
 */
final class CustomerController extends Controller
{
    private Customer $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Customer();
    }

    public function index(): void
    {
        $this->authorize('customers.view');
        $keyword = $this->request->string('q');
        $result  = $this->model->search($keyword, $this->request->int('page', 1));
        $this->view('customers.index', [
            'title'     => 'Customers',
            'customers' => $result['data'],
            'meta'      => $result,
            'keyword'   => $keyword,
        ]);
    }

    public function create(): void
    {
        $this->authorize('customers.create');
        $this->view('customers.form', ['title' => 'New Customer', 'customer' => null]);
    }

    public function edit(string $id): void
    {
        $this->authorize('customers.edit');
        $customer = $this->model->find((int) $id);
        if (!$customer) {
            Response::abort(404);
        }
        $this->view('customers.form', ['title' => 'Edit Customer', 'customer' => $customer]);
    }

    public function show(string $id): void
    {
        $this->authorize('customers.view');
        $customer = $this->model->find((int) $id);
        if (!$customer) {
            Response::abort(404);
        }
        $this->view('customers.show', [
            'title'    => $customer['name'],
            'customer' => $customer,
            'ledger'   => $this->model->ledger((int) $id),
            'balance'  => $this->model->balance((int) $id),
            'sales'    => Auth::can('sales.view')
                ? Database::getInstance()->fetchAll(
                    'SELECT * FROM sales WHERE customer_id = ? ORDER BY id DESC LIMIT 20',
                    [(int) $id]
                )
                : [],
        ]);
    }

    public function store(): void
    {
        $this->authorize('customers.create');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:2|max:160']);
        $id   = $this->model->create($this->payload($data));
        ActivityLog::record('customer.created', 'Customers', 'Created customer ' . $data['name'], 'customer', (int) $id);
        flash('success', 'Customer created.');
        redirect('customers/' . $id);
    }

    public function update(string $id): void
    {
        $this->authorize('customers.edit');
        $this->verifyCsrf();
        $data = $this->validate(['name' => 'required|min:2|max:160']);
        $this->model->update((int) $id, $this->payload($data));
        flash('success', 'Customer updated.');
        redirect('customers/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->authorize('customers.delete');
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Customer deleted.');
        redirect('customers');
    }

    public function collectPayment(string $id): void
    {
        $this->authorize('customers.payment');
        $this->verifyCsrf();
        $amount = $this->request->float('amount');
        if ($amount <= 0) {
            flash('error', 'Enter a valid amount.');
            redirect('customers/' . $id);
        }

        Database::getInstance()->transaction(function () use ($id, $amount): void {
            // Apply payment to oldest unpaid sales first.
            $due = Database::getInstance()->fetchAll(
                'SELECT id, due FROM sales WHERE customer_id = ? AND due > 0 AND status != "cancelled" ORDER BY id ASC',
                [(int) $id]
            );
            $remaining = $amount;
            foreach ($due as $sale) {
                if ($remaining <= 0) {
                    break;
                }
                $applied = min($remaining, (float) $sale['due']);
                Database::getInstance()->query(
                    'UPDATE sales SET paid = paid + ?, due = due - ?,
                     payment_status = CASE WHEN due - ? <= 0 THEN "paid" ELSE "partial" END
                     WHERE id = ?',
                    [$applied, $applied, $applied, $sale['id']]
                );
                $remaining -= $applied;
            }

            PaymentService::record(
                'customer', (int) $id, 'in', $amount,
                $this->request->string('method', 'cash'), 'customer', (int) $id,
                'Due collection'
            );
        });

        ActivityLog::record('customer.payment', 'Customers', 'Collected ' . money($amount) . ' from customer #' . $id);
        flash('success', 'Payment of ' . money($amount) . ' collected.');
        redirect('customers/' . $id);
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
            'type'            => $this->request->string('type', 'retail'),
            'opening_balance' => $this->request->float('opening_balance'),
            'credit_limit'    => $this->request->float('credit_limit'),
            'status'          => 1,
        ];
    }
}
