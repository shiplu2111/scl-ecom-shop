<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * @group Public
 */
class ProductController extends BaseController
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Public Endpoint: Search and Filter Products
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'category_id', 'brand_id', 'min_price', 'max_price', 'per_page'
        ]);

        $products = $this->productService->fetchFiltered($filters);
        
        return $this->successResponse(
            ProductResource::collection($products)->response()->getData(true),
            'Products retrieved successfully'
        );
    }

    /**
     * Public Endpoint: Show Product details by Slug
     */
    public function show(string $slug)
    {
        $product = $this->productService->findBySlug($slug);
        
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        return $this->successResponse(new ProductResource($product), 'Product details fetched successfully');
    }

    /**
     * Admin Endpoint: Store a new Product with associated arrays (Variants)
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return $this->successResponse(new ProductResource($product), 'Product created successfully', 201);
    }

    /**
     * Admin Endpoint: Upload a specific Image extending the existing Product
     */
    public function uploadImage(Request $request, int $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_thumbnail' => 'boolean'
        ]);

        $this->productService->uploadImage($id, $request->file('image'), $request->boolean('is_thumbnail'));
        
        $product = $this->productService->find($id)->load(['variants', 'category', 'brand', 'images']);
        return $this->successResponse(new ProductResource($product), 'Image uploaded successfully');
    }
}
