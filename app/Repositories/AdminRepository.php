<?php

namespace App\Repositories;

use App\Models\Admin;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function updateDeviceId(int $adminId, string $deviceId)
    {
        return $this->model->where('id', $adminId)->update(['device_id' => $deviceId]);
    }

    public function clearDeviceId(int $adminId)
    {
        return $this->model->where('id', $adminId)->update(['device_id' => null]);
    }
}
