<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Product
 */
class ProductController extends BaseController
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'brand_id', 'min_price', 'max_price', 'rating', 'sort', 'is_featured', 'is_flash_sale', 'is_best_seller', 'per_page']);
        $products = $this->productService->fetchFiltered($filters);
        return $this->successResponse(ProductResource::collection($products)->response()->getData(true), 'All products retrieved successfully');
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return $this->successResponse(new ProductResource($product), 'Product created successfully', 201);
    }

    public function show(int $id)
    {
        $product = $this->productService->find($id)->load(['variants', 'category', 'brand', 'images', 'seoMetadata']);
        return $this->successResponse(new ProductResource($product), 'Product details fetched successfully');
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = $this->productService->updateProduct($id, $request->validated());
        return $this->successResponse(new ProductResource($product), 'Product updated successfully');
    }

    public function destroy(int $id)
    {
        $this->productService->deleteProduct($id);
        return $this->successResponse(null, 'Product deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        $this->productService->bulkDeleteProducts($request->ids);
        return $this->successResponse(null, 'Products deleted successfully');
    }

    public function uploadImage(Request $request, int $id)
    {
        $request->validate([
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images'       => 'nullable|array',
            'images.*'     => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_thumbnail' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            $this->productService->uploadImage($id, $request->file('image'), $request->boolean('is_thumbnail'));
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                 $this->productService->uploadImage($id, $file, false);
            }
        }
        
        $product = $this->productService->find($id)->load(['variants', 'category', 'brand', 'images']);
        return $this->successResponse(new ProductResource($product), 'Images uploaded successfully');
    }

    public function export(Request $request)
    {
        $ids = $request->has('ids') ? explode(',', $request->ids) : [];
        $callback = $this->productService->exportProducts($ids);
        
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
