<?php

namespace App\Services;

use App\Repositories\AdminRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AdminAuthService extends BaseService
{
    protected AdminRepositoryInterface $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function login(array $credentials, ?string $deviceId = null)
    {
        $credentials['is_active'] = 1;
        
        if (! $token = auth('admin')->attempt($credentials)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials or account inactive.']]);
        }

        $admin = auth('admin')->user();

        // Single device login logic mapping
        if ($deviceId) {
            $this->adminRepository->updateDeviceId($admin->id, $deviceId);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        $admin = auth('admin')->user();
        if ($admin) {
            $this->adminRepository->clearDeviceId($admin->id);
            auth('admin')->logout();
        }
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('admin')->refresh());
    }

    protected function respondWithToken($token)
    {
        $admin = auth('admin')->user();
        
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'roles' => $admin->getRoleNames(),
                'permissions' => $admin->getAllPermissions()->pluck('name')->toArray()
            ]
        ];
    }
}
