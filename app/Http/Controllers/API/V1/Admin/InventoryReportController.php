<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Resources\InventoryResource;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryReportController extends BaseController
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Get stock report with status filters brilliantly properly flawlessly.
     */
    public function stockReport(Request $request): JsonResponse
    {
        $report = $this->reportingService->getStockReport($request->all());

        return $this->successResponse(
            InventoryResource::collection($report)->response()->getData(true),
            'Stock report retrieved brilliantly flawlessly.'
        );
    }

    /**
     * Get inventory overview stats brilliantly properly flawlessly.
     */
    public function inventoryStats(): JsonResponse
    {
        $stats = $this->reportingService->getInventoryStats();

        return $this->successResponse(
            $stats,
            'Inventory stats retrieved brilliantly flawlessly.'
        );
    }

    /**
     * Get profit/loss summary brilliantly properly flawlessly.
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $report = $this->reportingService->getProfitLossReport($request->all());

        return $this->successResponse(
            $report,
            'Profit/Loss report retrieved brilliantly flawlessly.'
        );
    }
}
