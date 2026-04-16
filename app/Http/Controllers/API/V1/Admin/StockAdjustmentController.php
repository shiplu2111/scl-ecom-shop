<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Resources\Admin\StockAdjustmentResource;
use App\Http\Resources\InventoryResource;
use App\Services\StockAdjustmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentController extends BaseController
{
    protected StockAdjustmentService $adjustmentService;

    public function __construct(StockAdjustmentService $adjustmentService)
    {
        $this->adjustmentService = $adjustmentService;
    }

    /**
     * Display current stock adjustments list brilliantly properly flawlessly.
     */
    public function index(Request $request): JsonResponse
    {
        $adjustments = $this->adjustmentService->fetchAdjustments($request->all());

        return $this->successResponse(
            StockAdjustmentResource::collection($adjustments)->response()->getData(true),
            'Stock adjustments fetched brilliantly flawlessly.'
        );
    }

    /**
     * Store new stock adjustment record brilliantly properly flawlessly.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'            => 'required|in:purchase_return,customer_return,damage,lost,manual',
            'adjustable_id'   => 'nullable|integer',
            'adjustable_type' => 'nullable|string',
            'reason'          => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
            'total_amount'    => 'required|numeric|min:0',
            'items'           => 'required|array|min:1',
            'items.*.sku'     => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $adjustment = $this->adjustmentService->createAdjustment($request->all());
            return $this->successResponse($adjustment, 'Stock adjustment recorded brilliantly properly.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Display the specified stock adjustment record brilliantly properly flawlessly.
     */
    public function show($id): JsonResponse
    {
        $adjustment = \App\Models\StockAdjustment::with(['items.variant.product', 'admin'])->find($id);

        if (!$adjustment) {
            return $this->errorResponse('Stock adjustment not found', null, 404);
        }

        return $this->successResponse(
            new StockAdjustmentResource($adjustment),
            'Stock adjustment details fetched brilliantly flawlessly.'
        );
    }
}
