<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');

        $query = FlashSale::query()->withCount('items');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $sales = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Flash sales retrieved flawlessly.',
            'data' => $sales
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.sale_price' => 'required|numeric|min:0',
            'items.*.quantity_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->is_featured) {
                FlashSale::where('id', '!=', 0)->update(['is_featured' => false]);
            }

            $flashSale = FlashSale::create($request->only(['name', 'description', 'start_time', 'end_time', 'is_active', 'is_featured', 'banner_image']));

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = isset($itemData['variant_id']) ? ProductVariant::find($itemData['variant_id']) : null;
                
                // Snapshot the current price flawlessly properly
                $originalPrice = $variant ? $variant->price : $product->price;

                $flashSale->items()->create([
                    'product_id' => $itemData['product_id'],
                    'variant_id' => $itemData['variant_id'] ?? null,
                    'price' => $originalPrice,
                    'sale_price' => $itemData['sale_price'],
                    'quantity_limit' => $itemData['quantity_limit'] ?? null,
                    'sold_quantity' => 0
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Flash sale created brilliantly.',
                'data' => $flashSale->load(['items.product', 'items.variant'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create flash sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(FlashSale $flashSale)
    {
        $flashSale->load(['items.product', 'items.variant']);
        $data = $flashSale->toArray();

        foreach ($data['items'] as &$item) {
            $sku = null;
            if (!empty($item['variant_id']) && !empty($item['variant'])) {
                $sku = $item['variant']['sku'];
            } elseif (!empty($item['product_id']) && !empty($item['product'])) {
                $sku = $item['product']['sku'];
            }

            $stock = 0;
            if ($sku) {
                // If it's a product with variants but no variant is selected, sum all variant stock
                if (empty($item['variant_id']) && !empty($item['product'])) {
                    $product = \App\Models\Product::with('variants')->find($item['product_id']);
                    if ($product && $product->variants->count() > 0) {
                        $stock = \App\Models\Inventory::whereIn('sku', $product->variants->pluck('sku'))->sum('quantity');
                    } else {
                        $stock = \App\Models\Inventory::where('sku', $sku)->value('quantity') ?? 0;
                    }
                } else {
                    $stock = \App\Models\Inventory::where('sku', $sku)->value('quantity') ?? 0;
                }
            }

            if (!empty($item['variant_id']) && !empty($item['variant'])) {
                $item['variant']['stock'] = (int) $stock;
            } elseif (!empty($item['product'])) {
                $item['product']['stock'] = (int) $stock;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Flash sale retrieved flawlessly.',
            'data' => $data
        ]);
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.sale_price' => 'required|numeric|min:0',
            'items.*.quantity_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->is_featured) {
                FlashSale::where('id', '!=', $flashSale->id)->update(['is_featured' => false]);
            }

            $flashSale->update($request->only(['name', 'description', 'start_time', 'end_time', 'is_active', 'is_featured', 'banner_image']));

            // Sync items carefully to preserve sold_quantity flawlessly brilliance properly
            $itemIds = [];
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = isset($itemData['variant_id']) ? ProductVariant::find($itemData['variant_id']) : null;
                
                // Snapshot the current price flawlessly properly
                $originalPrice = $variant ? $variant->price : $product->price;

                $item = $flashSale->items()->updateOrCreate(
                    [
                        'product_id' => $itemData['product_id'],
                        'variant_id' => $itemData['variant_id'] ?? null
                    ],
                    [
                        'price' => $originalPrice,
                        'sale_price' => $itemData['sale_price'],
                        'quantity_limit' => $itemData['quantity_limit'] ?? null,
                    ]
                );
                $itemIds[] = $item->id;
            }

            // Remove items that are no longer in the request flawlessly properly
            $flashSale->items()->whereNotIn('id', $itemIds)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Flash sale updated flawlessly.',
                'data' => $flashSale->load(['items.product', 'items.variant'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update flash sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return response()->json([
            'status' => true,
            'message' => 'Flash sale deleted successfully.'
        ]);
    }
}
