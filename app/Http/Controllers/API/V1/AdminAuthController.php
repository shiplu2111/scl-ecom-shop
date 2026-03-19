<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AdminAuthService;

/**
 * @group Admin
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
        return $this->successResponse(auth('admin')->user(), 'Admin profile retrieved successfully');
    }
}
