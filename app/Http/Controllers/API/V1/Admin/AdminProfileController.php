<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Services\Admin\AdminProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Admin
 * @subgroup Profile Management
 */
class AdminProfileController extends BaseController
{
    protected AdminProfileService $profileService;

    public function __construct(AdminProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get the authenticated admin profile.
     */
    public function show()
    {
        $admin = auth('admin')->user();
        return $this->successResponse([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => $admin->avatar_url,
            'roles' => $admin->getRoleNames(),
            'permissions' => $admin->getAllPermissions()->pluck('name'),
        ], 'Admin profile retrieved successfully');
    }

    /**
     * Update the authenticated admin profile information.
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $this->profileService->updateProfile($admin, $validator->validated());

        return $this->successResponse([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => $admin->avatar_url,
            'roles' => $admin->getRoleNames(),
            'permissions' => $admin->getAllPermissions()->pluck('name'),
        ], 'Profile updated successfully');
    }

    /**
     * Upload avatar for the authenticated admin.
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $admin = auth('admin')->user();
        $this->profileService->uploadAvatar($admin, $request->file('avatar'));

        return $this->successResponse([
            'avatar' => $admin->avatar_url,
        ], 'Avatar uploaded successfully');
    }
}
