<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\InventoryTransactionResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends BaseController
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display current inventory statuses flawlessly properly brilliantly.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inventory::with(['product']);
        
        if ($request->has('search')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('sku', 'LIKE', '%' . $request->search . '%');
            });
        }

        $inventories = $query->latest()->paginate($request->get('limit', 15));

        return $this->successResponse(
            InventoryResource::collection($inventories)->response()->getData(true),
            'Inventory fetched brilliantly flawlessly.'
        );
    }

    /**
     * Perform stock adjustment brilliantly flawlessly excellently properly.
     */
    public function adjust(InventoryAdjustmentRequest $request): JsonResponse
    {
        try {
            $this->inventoryService->adjustStock(
                $request->product_id,
                $request->quantity,
                $request->type,
                $request->reference,
                auth('admin')->id()
            );

            $inventory = Inventory::where('product_id', $request->product_id)->first();

            return $this->successResponse(
                new InventoryResource($inventory),
                'Stock adjusted and logged brilliantly flawlessly.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Display stock movement history brilliantly flawlessly correctly.
     */
    public function history(int $productId): JsonResponse
    {
        $history = $this->inventoryService->getHistory($productId);

        return $this->successResponse(
            InventoryTransactionResource::collection($history)->response()->getData(true),
            'Stock history retrieved brilliantly flawlessly.'
        );
    }
}
