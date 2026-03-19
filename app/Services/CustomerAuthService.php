<?php

namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthService extends BaseService
{
    protected UserRepositoryInterface $userRepository;
    protected OtpService $otpService;

    public function __construct(UserRepositoryInterface $userRepository, OtpService $otpService)
    {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
    }

    public function register(array $data)
    {
        $identity = $data['email'] ?? $data['phone'];
        if (!$this->otpService->isVerified($identity, 'register')) {
            throw ValidationException::withMessages(['otp' => ['Your identity has not been verified via OTP. Please verify first.']]);
        }

        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);
        
        $token = auth('api')->login($user);
        
        if (isset($data['device_id'])) {
            $this->userRepository->updateDeviceId($user->id, $data['device_id']);
        }

        return $this->respondWithToken($token);
    }

    public function login(array $credentials, ?string $deviceId = null, ?string $otp = null)
    {
        // Optional OTP check on Login
        if ($otp) {
            $identity = $credentials['email'] ?? $credentials['phone'] ?? null;
            if (!$this->otpService->isVerified($identity, 'login')) {
                throw ValidationException::withMessages(['otp' => ['Mandatory login OTP verification failed.']]);
            }
        }

        if (! $token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        
        $user = auth('api')->user();

        // Single device login logic mapping
        if ($deviceId) {
            $this->userRepository->updateDeviceId($user->id, $deviceId);
        }

        return $this->respondWithToken($token);
    }

    public function resetPassword(string $identity, string $newPassword)
    {
        if (!$this->otpService->isVerified($identity, 'password_reset')) {
            throw ValidationException::withMessages(['otp' => ['You must verify an OTP before resetting your password.']]);
        }

        $user = $this->userRepository->findByEmailOrPhone($identity);
        
        if (!$user) {
            throw ValidationException::withMessages(['identity' => ['User not found.']]);
        }

        $this->userRepository->update($user->id, ['password' => Hash::make($newPassword)]);
        return true;
    }

    public function logout()
    {
        $user = auth('api')->user();
        if ($user) {
            $this->userRepository->clearDeviceId($user->id);
            auth('api')->logout();
        }
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
    
    public function me()
    {
        return auth('api')->user();
    }

    public function changePassword(string $currentPassword, string $newPassword)
    {
        $user = auth('api')->user();

        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['Incorrect current password.']]);
        }

        $this->userRepository->update($user->id, ['password' => Hash::make($newPassword)]);
        
        return true;
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }
}
