<?php

namespace App\Repositories;

interface UserAddressRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserAddresses(int $userId);
    public function unsetUserDefaultAddresses(int $userId);
}
