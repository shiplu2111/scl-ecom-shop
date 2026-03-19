<?php

namespace App\Repositories;

use App\Models\UserAddress;

class UserAddressRepository extends BaseRepository implements UserAddressRepositoryInterface
{
    public function __construct(UserAddress $model)
    {
        parent::__construct($model);
    }

    public function getUserAddresses(int $userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function unsetUserDefaultAddresses(int $userId)
    {
        return $this->model->where('user_id', $userId)->update(['is_default' => false]);
    }
}
