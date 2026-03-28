<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats()
    {
        $now = Carbon::now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        return [
            'total_orders'     => $this->getMetricWithGrowth(Order::class, $thisMonthStart, $lastMonthStart, $lastMonthEnd),
            'total_revenue'    => $this->getRevenueWithGrowth($thisMonthStart, $lastMonthStart, $lastMonthEnd),
            'total_customers'  => $this->getMetricWithGrowth(User::class, $thisMonthStart, $lastMonthStart, $lastMonthEnd),
            'total_products'   => $this->getMetricWithGrowth(Product::class, $thisMonthStart, $lastMonthStart, $lastMonthEnd),
            'pending_orders'   => $this->getMetricWithGrowth(Order::class, $thisMonthStart, $lastMonthStart, $lastMonthEnd, 'order_status', 'pending'),
            'delivered_orders' => $this->getMetricWithGrowth(Order::class, $thisMonthStart, $lastMonthStart, $lastMonthEnd, 'order_status', 'delivered'),
        ];
    }

    public function getCharts()
    {
        $months = collect();
        for ($i = 6; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->format('M'));
        }

        $salesOverview = DB::table('orders')
            ->select(DB::raw("DATE_FORMAT(created_at, '%b') as month"), DB::raw("SUM(grand_total) as total"))
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->whereIn('order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month');

        $ordersRevenue = DB::table('orders')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b') as month"), 
                DB::raw("COUNT(*) as count"),
                DB::raw("SUM(grand_total) as revenue")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $charts = $months->map(function($m) use ($salesOverview, $ordersRevenue) {
            $data = $ordersRevenue->get($m);
            return [
                'month'   => $m,
                'sales'   => (float) $salesOverview->get($m, 0),
                'orders'  => (int) ($data->count ?? 0),
                'revenue' => (float) ($data->revenue ?? 0),
            ];
        });

        return $charts;
    }

    public function getRecentOrders(int $limit = 10)
    {
        return Order::with('user')->latest()->limit($limit)->get();
    }

    private function getMetricWithGrowth($model, $thisMonthStart, $lastMonthStart, $lastMonthEnd, $column = null, $value = null)
    {
        $query = $model::query();
        if ($column && $value) {
            $query->where($column, $value);
        }
        
        $current = (clone $query)->where('created_at', '>=', $thisMonthStart)->count();
        $previous = (clone $query)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $total = $query->count();

        return [
            'value'  => $total,
            'growth' => $this->calculateGrowth($current, $previous)
        ];
    }

    private function getRevenueWithGrowth($thisMonthStart, $lastMonthStart, $lastMonthEnd)
    {
        // Revenue is calculated as total_selling_price - total_buying_price (Excluding delivery charges)
        $current = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->where('orders.created_at', '>=', $thisMonthStart)
            ->select(DB::raw('SUM(order_items.total_price - (COALESCE(order_items.buying_price, 0) * order_items.quantity)) as profit'))
            ->first()->profit ?? 0;

        $previous = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->whereBetween('orders.created_at', [$lastMonthStart, $lastMonthEnd])
            ->select(DB::raw('SUM(order_items.total_price - (COALESCE(order_items.buying_price, 0) * order_items.quantity)) as profit'))
            ->first()->profit ?? 0;

        $total = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->select(DB::raw('SUM(order_items.total_price - (COALESCE(order_items.buying_price, 0) * order_items.quantity)) as profit'))
            ->first()->profit ?? 0;

        return [
            'value'  => $total,
            'growth' => $this->calculateGrowth($current, $previous)
        ];
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
