<?php

namespace App\Http\Controllers\API\V1;

use App\Services\OtpService;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Auth
 */
class OtpController extends BaseController
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'type'     => 'required|in:register,login,password_reset'
        ]);

        $this->otpService->generateAndSend($request->identity, $request->type);

        return $this->successResponse([], 'OTP successfully dispatched');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'otp'      => 'required|string|size:6',
            'type'     => 'required|in:register,login,password_reset',
            'name'     => 'nullable|string'
        ]);

        $this->otpService->verify($request->identity, $request->otp, $request->type);

        // If it's for login or specifically requested auto-registration (e.g. Guest Checkout)
        if ($request->type === 'login' || ($request->type === 'register' && $request->boolean('auto_register'))) {
            $authService = app(\App\Services\CustomerAuthService::class);
            $response = $authService->loginOrRegisterViaOtp(
                $request->identity, 
                $request->type, 
                $request->only('name', 'device_id')
            );
            return $this->successResponse($response, 'OTP verified and user authenticated.');
        }

        return $this->successResponse([], 'OTP successfully verified');
    }
}
