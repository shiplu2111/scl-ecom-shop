<?php

namespace App\Services;

use App\Repositories\UserAddressRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserService extends BaseService
{
    protected UserRepositoryInterface $userRepository;
    protected UserAddressRepositoryInterface $addressRepository;

    public function __construct(UserRepositoryInterface $userRepository, UserAddressRepositoryInterface $addressRepository)
    {
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
    }

    public function updateProfile(int $userId, array $data)
    {
        return $this->userRepository->update($userId, $data);
    }

    public function uploadAvatar(int $userId, UploadedFile $file)
    {
        $user = $this->userRepository->find($userId);

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars', 'public');
        $this->userRepository->update($userId, ['avatar' => $path]);

        return $path;
    }

    public function createAddress(int $userId, array $data)
    {
        if (isset($data['is_default']) && $data['is_default']) {
            $this->addressRepository->unsetUserDefaultAddresses($userId);
        } else {
            // If it's the very first address, enforce it as default
            $existing = $this->addressRepository->getUserAddresses($userId);
            if ($existing->isEmpty()) {
                $data['is_default'] = true;
            }
        }

        $data['user_id'] = $userId;
        return $this->addressRepository->create($data);
    }

    public function updateAddress(int $userId, int $addressId, array $data)
    {
        $address = $this->addressRepository->find($addressId);

        // Ensure authorization over address
        if ($address->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        if (isset($data['is_default']) && $data['is_default']) {
            $this->addressRepository->unsetUserDefaultAddresses($userId);
        }

        return $this->addressRepository->update($addressId, $data);
    }

    public function deleteAddress(int $userId, int $addressId)
    {
        $address = $this->addressRepository->find($addressId);

        if ($address->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        // If deleting a default address, maybe set another address as default?
        // Basic requirement doesn't mandate it, but keeping data clean:
        $isDefault = $address->is_default;
        $this->addressRepository->delete($addressId);

        if ($isDefault) {
            $remaining = $this->addressRepository->getUserAddresses($userId);
            if ($remaining->isNotEmpty()) {
                $this->addressRepository->update($remaining->first()->id, ['is_default' => true]);
            }
        }

        return true;
    }

    public function setDefaultAddress(int $userId, int $addressId)
    {
        $address = $this->addressRepository->find($addressId);

        if ($address->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        $this->addressRepository->unsetUserDefaultAddresses($userId);
        return $this->addressRepository->update($addressId, ['is_default' => true]);
    }
}
