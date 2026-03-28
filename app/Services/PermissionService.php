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
        $permissions = $this->permissionRepository->getAdminPermissions();
        
        return $permissions->reject(function ($permission) {
            return str_starts_with($permission->name, 'dashboard_');
        })->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            if (count($parts) > 1) {
                $lastPart = end($parts);
                if (in_array($lastPart, ['create', 'read', 'update', 'delete'])) {
                    array_pop($parts);
                    return implode('_', $parts);
                }
            }
            
            // Special cases mapping
            if (str_contains($permission->name, 'settings')) {
                return 'settings';
            }
            
            return 'other';
        });
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
