<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Inventory
 */
class InventoryController extends BaseController
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'reason' => 'required|string|max:255'
        ]);

        $variant = $this->resolveVariant($id);

        $this->inventoryService->updateStock(
            $variant, 
            $request->stock, 
            $request->reason, 
            auth()->id()
        );

        return $this->successResponse($variant->refresh(), 'Stock updated successfully.');
    }

    public function history($id)
    {
        $variant = $this->resolveVariant($id);
        $history = $variant->inventoryHistories()->with('user:id,name,email')->latest()->paginate(20);
        return $this->successResponse($history, 'Inventory history retrieved successfully.');
    }

    /**
     * Resolve ID to a ProductVariant.
     * Checks Variant ID first, then tries Product ID and returns its first variant.
     */
    protected function resolveVariant($id): ProductVariant
    {
        // Try Variant ID first
        $variant = ProductVariant::find($id);
        if ($variant) return $variant;

        // Try Product ID fallback
        $product = \App\Models\Product::find($id);
        if ($product) {
            if ($product->variants()->exists()) {
                return $product->variants()->first();
            }
            
            // Lazy-create variant for simple products created before the system update
            return $product->variants()->create([
                'sku'   => $product->sku,
                'price' => $product->price,
                'stock' => 0,
            ]);
        }

        abort(404, 'Inventory source not found for the provided ID.');
    }
}
