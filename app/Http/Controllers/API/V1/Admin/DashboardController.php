<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Services\Admin\DashboardService;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Dashboard
 */
class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard statistics with growth percentages.
     */
    public function stats()
    {
        $stats = $this->dashboardService->getStats();
        return $this->successResponse($stats, 'Dashboard statistics fetched successfully');
    }

    /**
     * Get chart data for sales and orders.
     */
    public function charts()
    {
        $charts = $this->dashboardService->getCharts();
        return $this->successResponse($charts, 'Chart data fetched successfully');
    }

    /**
     * Get recent orders.
     */
    public function recentOrders()
    {
        $orders = $this->dashboardService->getRecentOrders();
        return $this->successResponse(OrderResource::collection($orders), 'Recent orders fetched successfully');
    }
}
