<?php

namespace App\Services;

use App\Repositories\RoleRepositoryInterface;

class RoleService extends BaseService
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function find(int $id)
    {
        return $this->roleRepository->find($id);
    }

    public function getAllAdminRoles()
    {
        return $this->roleRepository->getAdminRoles();
    }

    public function createRole(array $data)
    {
        $data['guard_name'] = 'admin';
        return $this->roleRepository->create($data);
    }

    public function updateRole(int $id, array $data)
    {
        return $this->roleRepository->update($id, $data);
    }

    public function deleteRole(int $id)
    {
        return $this->roleRepository->delete($id);
    }

    public function assignPermissionsToRole(int $roleId, array $permissions)
    {
        $role = $this->roleRepository->find($roleId);
        $role->syncPermissions($permissions); // Spatie handles this implicitly onto mapped structures
        return $role;
    }
}
