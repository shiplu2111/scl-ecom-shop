<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Http\Requests\UserAddressRequest;
use App\Http\Resources\UserAddressResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;

/**
 * @group Customer
 */
class UserController extends BaseController
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function profile()
    {
        $user = auth()->user()->load('addresses');
        return $this->successResponse(new UserResource($user), 'User profile fetched successfully');
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $this->userService->updateProfile(auth()->id(), $request->validated());
        
        $user = auth()->user()->fresh()->load('addresses');
        return $this->successResponse(new UserResource($user), 'Profile updated successfully');
    }

    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $this->userService->uploadAvatar(auth()->id(), $request->file('avatar'));

        $user = auth()->user()->fresh()->load('addresses');
        return $this->successResponse(new UserResource($user), 'Avatar uploaded successfully');
    }

    public function listAddresses()
    {
        $addresses = auth()->user()->addresses()->with(['division', 'district', 'thana'])->get();
        return $this->successResponse(UserAddressResource::collection($addresses), 'Addresses retrieved successfully');
    }

    public function createAddress(UserAddressRequest $request)
    {
        $address = $this->userService->createAddress(auth()->id(), $request->validated());
        $address->load(['division', 'district', 'thana']);
        return $this->successResponse(new UserAddressResource($address), 'Address created successfully', 201);
    }

    public function updateAddress(UserAddressRequest $request, int $id)
    {
        $address = $this->userService->updateAddress(auth()->id(), $id, $request->validated());
        $updated = \App\Models\UserAddress::with(['division', 'district', 'thana'])->find($id);
        return $this->successResponse(new UserAddressResource($updated), 'Address updated successfully');
    }

    public function deleteAddress(int $id)
    {
        $this->userService->deleteAddress(auth()->id(), $id);
        return $this->successResponse([], 'Address deleted successfully');
    }

    public function setDefaultAddress(int $id)
    {
        $this->userService->setDefaultAddress(auth()->id(), $id);
        return $this->successResponse([], 'Address updated as default successfully');
    }
}
