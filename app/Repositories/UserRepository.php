<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmailOrPhone(string $identity)
    {
        return $this->model->where('email', $identity)
                           ->orWhere('phone', $identity)
                           ->first();
    }

    public function updateDeviceId(int $userId, string $deviceId)
    {
        return $this->model->where('id', $userId)->update(['device_id' => $deviceId]);
    }

    public function clearDeviceId(int $userId)
    {
        return $this->model->where('id', $userId)->update(['device_id' => null]);
    }
}
