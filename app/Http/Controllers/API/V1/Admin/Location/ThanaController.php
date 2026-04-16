<?php

namespace App\Http\Controllers\API\V1\Admin\Location;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Location\StoreThanaRequest;
use App\Http\Requests\Admin\Location\UpdateThanaRequest;
use App\Http\Resources\Location\ThanaResource;
use App\Services\Location\LocationService;
use App\Traits\ApiResponseTrait;

/**
 * @group Admin
 * @subgroup Location
 */
class ThanaController extends Controller
{
    use ApiResponseTrait;

    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index()
    {
        $thanas = $this->locationService->getAllThanas();
        return $this->successResponse(ThanaResource::collection($thanas), 'Thanas retrieved successfully');
    }

    public function store(StoreThanaRequest $request)
    {
        $thana = $this->locationService->createThana($request->validated());
        $thana->load('district', 'district.division');
        return $this->successResponse(new ThanaResource($thana), 'Thana created successfully', 201);
    }

    public function show($id)
    {
        $thana = $this->locationService->getThana($id);
        return $this->successResponse(new ThanaResource($thana), 'Thana retrieved successfully');
    }

    public function update(UpdateThanaRequest $request, $id)
    {
        $thana = $this->locationService->updateThana($id, $request->validated());
        $thana->load('district', 'district.division');
        return $this->successResponse(new ThanaResource($thana), 'Thana updated successfully');
    }

    public function destroy($id)
    {
        $this->locationService->deleteThana($id);
        return $this->successResponse([], 'Thana deleted successfully');
    }
}
