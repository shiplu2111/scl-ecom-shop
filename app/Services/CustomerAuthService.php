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

        // Check if user already exists (e.g. from guest checkout)
        $user = $this->userRepository->findByEmailOrPhone($identity);
        
        if ($user) {
            // Only allow transition if the user has no password (guest/checkout placeholder)
            if ($user->password !== null) {
                $field = isset($data['email']) ? 'email' : 'phone';
                throw ValidationException::withMessages([$field => ["This {$field} is already associated with a registered account."]]);
            }
            
            // "Take over" the guest account
            $updateData = array_merge($data, [
                'password' => Hash::make($data['password']),
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?: (isset($data['email']) ? now() : null),
            ]);
            
            $user = $this->userRepository->update($user->id, $updateData);
        } else {
            $data['password'] = Hash::make($data['password']);
            $user = $this->userRepository->create($data);
        }
        
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

        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties(\App\Helpers\DeviceHelper::getDeviceInfo())
            ->log('login');

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
            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(\App\Helpers\DeviceHelper::getDeviceInfo())
                ->log('logout');

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

    public function findOrCreateSocialUser(string $provider, $socialUser)
    {
        $user = $this->userRepository->findByProvider($provider, $socialUser->getId());

        if (!$user) {
            // Check if user with same email exists
            $user = $this->userRepository->findByEmailOrPhone($socialUser->getEmail());

            if ($user) {
                // Link account
                $this->userRepository->update($user->id, [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $user->avatar ?: $socialUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = $this->userRepository->create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => null, // Social user has no password
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
        }

        $token = auth('api')->login($user);
        return $this->respondWithToken($token);
    }

    public function loginOrRegisterViaOtp(string $identity, string $type, array $extraData = [])
    {
        if (!$this->otpService->isVerified($identity, $type)) {
            throw ValidationException::withMessages(['otp' => ['Identity verification failed or expired.']]);
        }

        $user = $this->userRepository->findByEmailOrPhone($identity);

        if (!$user && $type === 'register') {
            // Create new user for guest checkout
            $isEmail = filter_var($identity, FILTER_VALIDATE_EMAIL);
            $name = !empty($extraData['name']) ? $extraData['name'] : 'Guest User';
            
            $user = $this->userRepository->create([
                'name' => $name,
                'email' => $isEmail ? $identity : ($extraData['email'] ?? null),
                'phone' => !$isEmail ? $identity : ($extraData['phone'] ?? null),
                'password' => null, // No password for OTP-created guest users
                'is_active' => true,
                'email_verified_at' => $isEmail ? now() : null,
            ]);
        }

        if (!$user) {
            throw ValidationException::withMessages(['identity' => ['User not found.']]);
        }

        $token = auth('api')->login($user);
        
        if (isset($extraData['device_id'])) {
            $this->userRepository->updateDeviceId($user->id, $extraData['device_id']);
        }

        return $this->respondWithToken($token);
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
