<?php

namespace App\Repositories;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    // Specific queries extending Spatie mappings natively over the admin guard
    public function getAdminRoles();
}
