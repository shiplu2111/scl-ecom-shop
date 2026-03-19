<?php

namespace App\Http\Controllers\Api\V1\Admin\Location;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Location\StoreDistrictRequest;
use App\Http\Requests\Admin\Location\UpdateDistrictRequest;
use App\Http\Resources\Location\DistrictResource;
use App\Services\Location\LocationService;
use App\Traits\ApiResponseTrait;

/**
 * @group Admin
 */
class DistrictController extends Controller
{
    use ApiResponseTrait;

    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index()
    {
        $districts = $this->locationService->getAllDistricts();
        return $this->successResponse(DistrictResource::collection($districts), 'Districts retrieved successfully');
    }

    public function store(StoreDistrictRequest $request)
    {
        $district = $this->locationService->createDistrict($request->validated());
        $district->load('division');
        return $this->successResponse(new DistrictResource($district), 'District created successfully', 201);
    }

    public function show($id)
    {
        $district = $this->locationService->getDistrict($id);
        return $this->successResponse(new DistrictResource($district), 'District retrieved successfully');
    }

    public function update(UpdateDistrictRequest $request, $id)
    {
        $district = $this->locationService->updateDistrict($id, $request->validated());
        $district->load('division');
        return $this->successResponse(new DistrictResource($district), 'District updated successfully');
    }

    public function destroy($id)
    {
        $this->locationService->deleteDistrict($id);
        return $this->successResponse([], 'District deleted successfully');
    }
}
