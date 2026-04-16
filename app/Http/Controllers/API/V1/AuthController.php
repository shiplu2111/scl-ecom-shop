<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\CustomerAuthService;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Auth
 */
class AuthController extends BaseController
{
    protected CustomerAuthService $authService;

    public function __construct(CustomerAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $response = $this->authService->register($request->validated());
        return $this->successResponse($response, 'User successfully registered', 201);
    }

    public function login(LoginRequest $request)
    {
        $response = $this->authService->login($request->only('email', 'password'), $request->device_id, $request->otp);
        return $this->successResponse($response, 'User successfully logged in');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $this->authService->resetPassword($request->identity, $request->new_password);
        return $this->successResponse([], 'Password successfully reset.');
    }

    public function logout()
    {
        $this->authService->logout();
        return $this->successResponse([], 'User successfully signed out');
    }

    public function refresh()
    {
        $response = $this->authService->refresh();
        return $this->successResponse($response, 'Token refreshed successfully');
    }

    public function me()
    {
        return $this->successResponse($this->authService->me(), 'User profile retrieved successfully');
    }

    public function checkExists(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $query = \App\Models\User::query();
        if ($request->email) $query->where('email', $request->email);
        if ($request->phone) $query->orWhere('phone', $request->phone);

        $exists = $query->exists();

        return $this->successResponse(['exists' => $exists], 'User existence check completed');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);

        $this->authService->changePassword($request->current_password, $request->new_password);
        return $this->successResponse([], 'Password changed successfully');
    }
}
