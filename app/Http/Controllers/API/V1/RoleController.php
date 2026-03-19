<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\RoleRequest;
use App\Services\RoleService;

/**
 * @group Admin
 */
class RoleController extends BaseController
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->getAllAdminRoles()->load('permissions');
        return $this->successResponse($roles, 'Roles fetched successfully');
    }

    public function store(RoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());
        
        if ($request->has('permissions')) {
            $this->roleService->assignPermissionsToRole($role->id, $request->permissions);
            $role->load('permissions');
        }

        return $this->successResponse($role, 'Role created successfully', 201);
    }

    public function show($id)
    {
        $role = $this->roleService->find($id)->load('permissions');
        return $this->successResponse($role, 'Role fetched successfully');
    }

    public function update(RoleRequest $request, $id)
    {
        $role = $this->roleService->updateRole($id, $request->validated());
        
        if ($request->has('permissions')) {
            $this->roleService->assignPermissionsToRole($role->id, $request->permissions);
        }

        $role = $this->roleService->find($id)->load('permissions');
        return $this->successResponse($role, 'Role updated successfully');
    }

    public function destroy($id)
    {
        $this->roleService->deleteRole($id);
        return $this->successResponse([], 'Role deleted successfully');
    }
}
