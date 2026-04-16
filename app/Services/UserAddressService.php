<?php

namespace App\Services;

use App\Repositories\UserAddressRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserAddressService extends BaseService
{
    protected UserAddressRepositoryInterface $addressRepo;

    public function __construct(UserAddressRepositoryInterface $addressRepo)
    {
        $this->addressRepo = $addressRepo;
    }

    public function getAllForUser(int $userId)
    {
        return $this->addressRepo->getUserAddresses($userId);
    }

    public function create(array $data)
    {
        $userId = Auth::id();
        $data['user_id'] = $userId;

        if ($data['is_default'] ?? false) {
            $this->addressRepo->unsetUserDefaultAddresses($userId);
        }

        return $this->addressRepo->create($data);
    }

    public function update(int $id, array $data)
    {
        $userId = Auth::id();
        $address = $this->addressRepo->find($id);

        if ($address->user_id !== $userId) {
            throw new \Exception('Unauthorized to specifically access this address.');
        }

        if ($data['is_default'] ?? false) {
            $this->addressRepo->unsetUserDefaultAddresses($userId);
        }

        return $this->addressRepo->update($id, $data);
    }

    public function delete(int $id)
    {
        $userId = Auth::id();
        $address = $this->addressRepo->find($id);

        if ($address->user_id !== $userId) {
            throw new \Exception('Unauthorized to delete this address.');
        }

        return $this->addressRepo->delete($id);
    }

    public function setDefault(int $id)
    {
        $userId = Auth::id();
        $address = $this->addressRepo->find($id);

        if ($address->user_id !== $userId) {
            throw new \Exception('Unauthorized access.');
        }

        $this->addressRepo->unsetUserDefaultAddresses($userId);
        return $this->addressRepo->update($id, ['is_default' => true]);
    }

    public function getDefaultAddress(int $userId)
    {
        return \App\Models\UserAddress::where('user_id', $userId)
            ->where('is_default', true)
            ->with(['division', 'district', 'thana'])
            ->first();
    }
}
