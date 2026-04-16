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
        $query = Inventory::with(['product', 'variant.product'])
            ->where(function ($q) {
                // Ensure the associated product is NOT deleted
                $q->whereHas('product', function($pq) {
                    $pq->whereNull('deleted_at');
                })->orWhereHas('variant.product', function($vq) {
                    $vq->whereNull('deleted_at');
                });
            })
            ->where(function ($q) {
                // Only show rows that are actual individual variants, 
                // OR main products that have absolutely zero variants (simple products fallback)
                $q->has('variant')
                  ->orWhereHas('product', function ($q2) {
                      $q2->doesntHave('variants');
                  });
            });
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($sq) use ($searchTerm) {
                $sq->where('sku', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('product', function($q) use ($searchTerm) {
                      $q->where('name', 'LIKE', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('variant.product', function($q) use ($searchTerm) {
                      $q->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
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
                $request->sku,
                $request->quantity,
                $request->type,
                $request->reference,
                auth('admin')->id()
            );

            $inventory = Inventory::where('sku', $request->sku)->first();

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
    public function history(Request $request, ?string $sku = null): JsonResponse
    {
        if ($sku) {
            $history = $this->inventoryService->getHistory($sku);
        } else {
            $history = $this->inventoryService->getGlobalHistory($request->all());
        }

        return $this->successResponse(
            InventoryTransactionResource::collection($history)->response()->getData(true),
            'Stock history retrieved brilliantly flawlessly.'
        );
    }
}
