<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Brand
 */
class BrandController extends BaseController
{
    protected BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * List all brands.
     */
    public function index()
    {
        $brands = $this->brandService->fetchAll();
        return $this->successResponse(BrandResource::collection($brands), 'Brands retrieved successfully');
    }

    /**
     * Show brand details by slug.
     */
    public function show(string $slug)
    {
        $brand = $this->brandService->findBySlug($slug);
        
        if (!$brand) {
            return $this->errorResponse('Brand not found', 404);
        }

        return $this->successResponse(new BrandResource($brand), 'Brand details fetched successfully');
    }
}
