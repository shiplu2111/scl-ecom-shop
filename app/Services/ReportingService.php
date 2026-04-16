<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingService extends BaseService
{
    /**
     * Get comprehensive stock report brilliantly flawlessly Properly.
     */
    public function getStockReport(array $filters = [])
    {
        $query = Inventory::with(['product', 'variant.product']);

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'low') {
                $query->whereColumn('quantity', '<=', 'low_stock_alert');
            } elseif ($filters['status'] === 'out') {
                $query->where('quantity', '<=', 0);
            }
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('sku', 'LIKE', "%$searchTerm%")
                  ->orWhereHas('product', fn($sq) => $sq->where('name', 'LIKE', "%$searchTerm%"))
                  ->orWhereHas('variant.product', fn($sq) => $sq->where('name', 'LIKE', "%$searchTerm%"));
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get basic stats for inventory dashboard brilliantly properly.
     */
    public function getInventoryStats()
    {
        return [
            'total_items'     => Inventory::count(),
            'total_quantity'  => (int) Inventory::sum('quantity'),
            'low_stock_count' => Inventory::whereColumn('quantity', '<=', 'low_stock_alert')->where('quantity', '>', 0)->count(),
            'out_of_stock'    => Inventory::where('quantity', '<=', 0)->count(),
            'stock_value'     => (float) Inventory::sum(DB::raw('quantity * buying_price')),
        ];
    }

    /**
     * Get profit/loss report summary brilliantly properly flawlessly correctly.
     */
    public function getProfitLossReport(array $filters = [])
    {
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->startOfMonth();
        $endDate   = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now()->endOfMonth();

        // 1. Sales Margin (from delivered orders)
        $sales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('SUM(order_items.buying_price * order_items.quantity) as total_cost')
            )
            ->first();

        // 2. Returns (Loss)
        $returns = StockAdjustment::where('type', 'purchase_return')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // 3. Damages (Loss)
        $damages = StockAdjustment::whereIn('type', ['damage', 'lost'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $revenue = (float) ($sales->total_revenue ?? 0);
        $cost    = (float) ($sales->total_cost ?? 0);
        $margin  = $revenue - $cost;
        $net_profit = $margin - $returns - $damages;

        return [
            'period' => [
                'from' => $startDate->toDateString(),
                'to'   => $endDate->toDateString(),
            ],
            'revenue'    => $revenue,
            'cost'       => $cost,
            'margin'     => $margin,
            'returns'    => (float) $returns,
            'damages'    => (float) $damages,
            'net_profit' => $net_profit,
        ];
    }
}
