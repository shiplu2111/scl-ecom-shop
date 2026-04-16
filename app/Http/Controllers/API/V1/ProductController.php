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
            'search', 'category_id', 'category_slug', 'categories', 'brand_id', 'brand_slug', 'brands',
            'min_price', 'max_price', 'per_page', 'page',
            'sort', 'colors', 'sizes',
            'is_featured', 'is_best_seller', 'is_flash_sale', 'special_offers'
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
     * Public Endpoint: Get all unique filter options (colors, sizes, brands, etc.)
     */
    public function filters()
    {
        $filters = $this->productService->getFilterOptions();
        return $this->successResponse($filters, 'Filter options retrieved successfully');
    }
}
