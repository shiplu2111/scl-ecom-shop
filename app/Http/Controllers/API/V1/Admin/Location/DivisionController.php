<?php

namespace App\Http\Controllers\Api\V1\Admin\Location;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Location\StoreDivisionRequest;
use App\Http\Requests\Admin\Location\UpdateDivisionRequest;
use App\Http\Resources\Location\DivisionResource;
use App\Services\Location\LocationService;
use App\Traits\ApiResponseTrait;

/**
 * @group Admin
 */
class DivisionController extends Controller
{
    use ApiResponseTrait;

    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index()
    {
        $divisions = $this->locationService->getAllDivisions();
        return $this->successResponse(DivisionResource::collection($divisions), 'Divisions retrieved successfully');
    }

    public function store(StoreDivisionRequest $request)
    {
        $division = $this->locationService->createDivision($request->validated());
        return $this->successResponse(new DivisionResource($division), 'Division created successfully', 201);
    }

    public function show($id)
    {
        $division = $this->locationService->getDivision($id);
        return $this->successResponse(new DivisionResource($division), 'Division retrieved successfully');
    }

    public function update(UpdateDivisionRequest $request, $id)
    {
        $division = $this->locationService->updateDivision($id, $request->validated());
        return $this->successResponse(new DivisionResource($division), 'Division updated successfully');
    }

    public function destroy($id)
    {
        $this->locationService->deleteDivision($id);
        return $this->successResponse([], 'Division deleted successfully');
    }
}
