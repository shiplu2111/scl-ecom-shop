<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\Request;

/**
 * @group Admin
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
     * List all brands for admin management.
     */
    public function index()
    {
        $brands = $this->brandService->fetchAll();
        return $this->successResponse(BrandResource::collection($brands), 'All brands retrieved successfully');
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->createBrand($request->validated());
        return $this->successResponse(new BrandResource($brand), 'Brand created successfully', 201);
    }

    public function show(int $id)
    {
        $brand = $this->brandService->fetchAll()->find($id)?->load('seoMetadata');
        
        if (!$brand) {
            return $this->errorResponse('Brand not found', 404);
        }

        return $this->successResponse(new BrandResource($brand), 'Brand details fetched successfully');
    }

    public function update(UpdateBrandRequest $request, int $id)
    {
        $brand = $this->brandService->updateBrand($id, $request->validated());
        return $this->successResponse(new BrandResource($brand), 'Brand updated successfully');
    }

    public function destroy(int $id)
    {
        $this->brandService->deleteBrand($id);
        return $this->successResponse(null, 'Brand deleted successfully');
    }
}
