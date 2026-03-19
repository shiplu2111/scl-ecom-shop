<?php

namespace App\Services;

use App\Repositories\PermissionRepositoryInterface;

class PermissionService extends BaseService
{
    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function find(int $id)
    {
        return $this->permissionRepository->find($id);
    }

    public function getAllAdminPermissions()
    {
        return $this->permissionRepository->getAdminPermissions();
    }

    public function createPermission(array $data)
    {
        $data['guard_name'] = 'admin';
        return $this->permissionRepository->create($data);
    }

    public function updatePermission(int $id, array $data)
    {
        return $this->permissionRepository->update($id, $data);
    }

    public function deletePermission(int $id)
    {
        return $this->permissionRepository->delete($id);
    }
}
