<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\UserAddressService;
use Illuminate\Http\Request;

/**
 * @group User
 * @subgroup Address Book
 */
class UserAddressController extends BaseController
{
    protected UserAddressService $addressService;

    public function __construct(UserAddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function index()
    {
        $addresses = $this->addressService->getAllForUser(auth()->id());
        $addresses->load(['division', 'district', 'thana']);
        return $this->successResponse(\App\Http\Resources\UserAddressResource::collection($addresses), 'Address book fetched successfully.');
    }

    public function default()
    {
        $address = $this->addressService->getDefaultAddress(auth()->id());
        if (!$address) return $this->successResponse(null, 'No default address found.');
        return $this->successResponse(new \App\Http\Resources\UserAddressResource($address), 'Default address retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id'  => 'required|exists:divisions,id',
            'district_id'  => 'required|exists:districts,id',
            'thana_id'     => 'required|exists:thanas,id',
            'address_line' => 'required|string',
            'postal_code'  => 'nullable|string',
            'is_default'   => 'boolean'
        ]);

        $address = $this->addressService->create($request->all());
        $address->load(['division', 'district', 'thana']);
        return $this->successResponse(new \App\Http\Resources\UserAddressResource($address), 'Address added successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'division_id'  => 'sometimes|exists:divisions,id',
            'district_id'  => 'sometimes|exists:districts,id',
            'thana_id'     => 'sometimes|exists:thanas,id',
            'address_line' => 'sometimes|string',
            'postal_code'  => 'nullable|string',
            'is_default'   => 'boolean'
        ]);

        $address = $this->addressService->update($id, $request->all());
        $address->load(['division', 'district', 'thana']);
        return $this->successResponse(new \App\Http\Resources\UserAddressResource($address), 'Address updated successfully.');
    }

    public function destroy($id)
    {
        $this->addressService->delete($id);
        return $this->successResponse([], 'Address deleted successfully.');
    }

    public function setDefault($id)
    {
        $address = $this->addressService->setDefault($id);
        $address->load(['division', 'district', 'thana']);
        return $this->successResponse(new \App\Http\Resources\UserAddressResource($address), 'Address set as default successfully.');
    }
}
