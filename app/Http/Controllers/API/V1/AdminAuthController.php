<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Services\AdminAuthService;

/**
 * @group Admin
 * @subgroup Auth
 */
class AdminAuthController extends BaseController
{
    protected AdminAuthService $authService;

    public function __construct(AdminAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $response = $this->authService->login($request->only('email', 'password'), $request->device_id);
        return $this->successResponse($response, 'Admin successfully logged in');
    }

    public function logout()
    {
        $this->authService->logout();
        return $this->successResponse([], 'Admin successfully signed out');
    }

    public function refresh()
    {
        $response = $this->authService->refresh();
        return $this->successResponse($response, 'Token refreshed successfully');
    }

    public function me()
    {
        $admin = auth('admin')->user();
        return $this->successResponse([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => $admin->avatar_url,
            'roles' => $admin->getRoleNames(),
            'permissions' => $admin->getAllPermissions()->pluck('name')->toArray()
        ], 'Admin profile retrieved successfully');
    }
}
