<?php

namespace App\Repositories;

interface AdminRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email);
    public function updateDeviceId(int $adminId, string $deviceId);
    public function clearDeviceId(int $adminId);
}
