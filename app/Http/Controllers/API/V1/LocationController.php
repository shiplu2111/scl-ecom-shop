<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Location\DivisionResource;
use App\Http\Resources\Location\DistrictResource;
use App\Http\Resources\Location\ThanaResource;
use App\Http\Requests\Public\Location\GetDistrictsRequest;
use App\Http\Requests\Public\Location\GetThanasRequest;
use App\Services\Location\LocationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Location
 */
class LocationController extends Controller
{
    use ApiResponseTrait;

    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function divisions()
    {
        $divisions = $this->locationService->getAllDivisions();
        return $this->successResponse(DivisionResource::collection($divisions), 'Divisions retrieved successfully');
    }

    public function districts(GetDistrictsRequest $request)
    {
        $districts = $this->locationService->getMinimalDistricts($request->input('division_id'));
        return $this->successResponse($districts, 'Districts retrieved successfully');
    }

    public function thanas(GetThanasRequest $request)
    {
        $thanas = $this->locationService->getMinimalThanas($request->input('district_id'));
        return $this->successResponse($thanas, 'Thanas retrieved successfully');
    }
}
