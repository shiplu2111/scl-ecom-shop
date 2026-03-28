<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Product
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
}
