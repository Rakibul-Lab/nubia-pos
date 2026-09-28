<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Dashboard;

/**
 * Dashboard controller: KPIs, charts and quick insights.
 *
 * @package App\Controllers
 */
final class DashboardController extends Controller
{
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
            default => [$today, $today],
        };

        if ($period !== 'custom' && !in_array($period, ['today', 'yesterday', 'week', 'month', 'year'], true)) {
            $period = 'today';
        }

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end, $period];
    }

    public function index(): void
    {
        $this->authorize('dashboard.view');

        [$start, $end, $period] = $this->range();
        $dashboard = new Dashboard();

        $trend = Auth::can('dashboard.charts') ? $dashboard->salesTrend($start, $end) : [];
        if ($trend !== []) {
            if (!Auth::can('dashboard.sales')) {
                unset($trend['sales']);
            }
            if (!Auth::can('dashboard.profit')) {
                unset($trend['profit']);
            }
            if (!Auth::can('dashboard.purchase')) {
                unset($trend['purchases']);
            }
        }

        $kpis = (Auth::can('dashboard.metrics')
            || Auth::can('dashboard.sales')
            || Auth::can('dashboard.profit')
            || Auth::can('dashboard.purchase')
            || Auth::can('dashboard.expenses')
            || Auth::can('dashboard.revenue')
            || Auth::can('dashboard.due_collection')
            || Auth::can('dashboard.due_payment')
            || Auth::can('dashboard.stock_value'))
            ? $dashboard->kpis($start, $end)
            : [];
        if ($kpis !== []) {
            if (!Auth::can('dashboard.sales')) {
                unset($kpis['sales']);
            }
            if (!Auth::can('dashboard.revenue')) {
                unset($kpis['revenue']);
            }
            if (!Auth::can('dashboard.profit')) {
                unset($kpis['profit']);
            }
            if (!Auth::can('dashboard.purchase')) {
                unset($kpis['purchase']);
            }
            if (!Auth::can('dashboard.stock_value')) {
                unset($kpis['stock_value']);
            }
            if (!Auth::can('dashboard.expenses')) {
                unset($kpis['expense']);
            }
            if (!Auth::can('dashboard.due_collection') && !Auth::can('dashboard.due_payment')) {
                unset($kpis['due_collect'], $kpis['due_pay'], $kpis['receivable'], $kpis['payable']);
            } else {
                if (!Auth::can('dashboard.due_collection')) {
                    unset($kpis['due_collect']);
                }
                if (!Auth::can('dashboard.due_payment')) {
                    unset($kpis['due_pay']);
                }
            }
        }

        $topProducts = Auth::can('sales.view') ? $dashboard->topProducts($start, $end) : [];
        if ($topProducts !== [] && !Auth::can('dashboard.revenue')) {
            foreach ($topProducts as &$row) {
                unset($row['revenue']);
            }
            unset($row);
        }

        $this->view('dashboard.index', [
            'title'         => 'Dashboard',
            'period'        => $period,
            'start'         => $start,
            'end'           => $end,
            'kpis'          => $kpis,
            'topProducts'   => $topProducts,
            'recentSales'   => Auth::can('sales.view') ? $dashboard->recentSales($start, $end) : [],
            'lowStock'      => Auth::can('stock.view') ? $dashboard->lowStock() : [],
            'trend'         => $trend,
            'payments'      => Auth::can('dashboard.charts') ? $dashboard->paymentBreakdown($start, $end) : [],
        ]);
    }

    public function chartData(): void
    {
        $this->authorize('dashboard.charts');
        [$start, $end] = $this->range();
        $dashboard     = new Dashboard();
        $trend         = $dashboard->salesTrend($start, $end);
        if (!Auth::can('dashboard.sales')) {
            unset($trend['sales']);
        }
        if (!Auth::can('dashboard.profit')) {
            unset($trend['profit']);
        }
        if (!Auth::can('dashboard.purchase')) {
            unset($trend['purchases']);
        }
        Response::json([
            'trend'    => $trend,
            'payments' => $dashboard->paymentBreakdown($start, $end),
        ]);
    }
}
