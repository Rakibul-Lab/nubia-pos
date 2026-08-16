<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

/**
 * Global search across products, invoices, customers, suppliers, IMEI, etc.
 *
 * @package App\Controllers
 */
final class SearchController extends Controller
{
    public function global(): void
    {
        $q = $this->request->string('q');
        if (mb_strlen($q) < 2) {
            Response::json(['results' => []]);
        }
        $like = '%' . $q . '%';
        $db   = Database::getInstance();

        $results = [];

        if (Auth::can('products.view')) {
            $products = $db->fetchAll(
                'SELECT DISTINCT p.id, p.name, p.sku
                 FROM products p
                 LEFT JOIN product_serials ps ON ps.product_id = p.id
                 WHERE p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?
                    OR ps.imei LIKE ? OR ps.serial_no LIKE ?
                 LIMIT 5',
                [$like, $like, $like, $like, $like]
            );
            foreach ($products as $p) {
                $results['Products'][] = ['title' => $p['name'], 'subtitle' => $p['sku'], 'url' => '/products/' . $p['id']];
            }
        }

        if (Auth::can('sales.view')) {
            $sales = $db->fetchAll('SELECT id, invoice_no, total FROM sales WHERE invoice_no LIKE ? LIMIT 5', [$like]);
            foreach ($sales as $s) {
                $results['Invoices'][] = ['title' => $s['invoice_no'], 'subtitle' => money($s['total']), 'url' => '/sales/' . $s['id']];
            }
        }

        if (Auth::can('customers.view')) {
            $customers = $db->fetchAll('SELECT id, name, phone FROM customers WHERE name LIKE ? OR phone LIKE ? LIMIT 5', [$like, $like]);
            foreach ($customers as $c) {
                $results['Customers'][] = ['title' => $c['name'], 'subtitle' => $c['phone'], 'url' => '/customers/' . $c['id']];
            }
        }

        if (Auth::can('suppliers.view')) {
            $suppliers = $db->fetchAll('SELECT id, name, phone FROM suppliers WHERE name LIKE ? OR phone LIKE ? LIMIT 5', [$like, $like]);
            foreach ($suppliers as $s) {
                $results['Suppliers'][] = ['title' => $s['name'], 'subtitle' => $s['phone'], 'url' => '/suppliers/' . $s['id']];
            }
        }

        if (Auth::can('products.serials.view') && Auth::can('products.view')) {
            $serials = $db->fetchAll('SELECT product_id, serial_no, imei FROM product_serials WHERE serial_no LIKE ? OR imei LIKE ? LIMIT 5', [$like, $like]);
            foreach ($serials as $s) {
                $results['Serial / IMEI'][] = ['title' => $s['imei'] ?: $s['serial_no'], 'subtitle' => 'Product #' . $s['product_id'], 'url' => '/products/' . $s['product_id']];
            }
        }

        Response::json(['results' => $results]);
    }
}
