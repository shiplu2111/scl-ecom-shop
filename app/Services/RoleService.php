<?php

namespace App\Services;

use App\Repositories\RoleRepositoryInterface;
use App\Models\Admin;

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
        $role = $this->roleRepository->update($id, $data);
        Admin::query()->update(['device_id' => null]);
        return $role;
    }

    public function deleteRole(int $id)
    {
        $role = $this->roleRepository->find($id);
        
        if ($role->users()->exists()) {
            throw new \Exception("Cannot delete role '{$role->name}' because it is assigned to one or more admins.");
        }
        
        $role = $this->roleRepository->delete($id);
        Admin::query()->update(['device_id' => null]);
        return $role;
    }

    public function assignPermissionsToRole(int $roleId, array $permissions)
    {
        $role = $this->roleRepository->find($roleId);
        $role->syncPermissions($permissions); // Spatie handles this implicitly onto mapped structures
        
        Admin::query()->update(['device_id' => null]);
        
        return $role;
    }
}
