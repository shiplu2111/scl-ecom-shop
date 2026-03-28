<?php

namespace App\Repositories;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmailOrPhone(string $identity);
    public function searchAndFilter(array $filters);
    public function toggleStatus(int $userId);
    public function updateDeviceId(int $userId, string $deviceId);
    public function clearDeviceId(int $userId);
}
