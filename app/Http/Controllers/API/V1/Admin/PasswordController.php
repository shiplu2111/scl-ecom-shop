<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Services\Admin\AdminPasswordService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Password Management
 */
class PasswordController extends BaseController
{
    protected AdminPasswordService $passwordService;

    public function __construct(AdminPasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * Send OTP for forget password.
     */
    public function forgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $status = $this->passwordService->sendForgetPasswordOtp($request->email);
        
        if (!$status) {
            // We return success even if email not found for security reasons (don't leak existence)
            // But usually for Admin panel, it's okay to be specific.
            return $this->errorResponse('If the email exists, an OTP has been sent.', 404);
        }

        return $this->successResponse(null, 'OTP sent to your email.');
    }

    /**
     * Reset password using OTP.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = $this->passwordService->resetPassword($request->all());

        if (!$status) {
            return $this->errorResponse('Invalid or expired OTP.', 422);
        }

        return $this->successResponse(null, 'Password reset successfully.');
    }

    /**
     * Change password while authenticated.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $result = $this->passwordService->changePassword(auth('admin')->user(), $request->all());

        if (!$result['status']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse(null, $result['message']);
    }
}
