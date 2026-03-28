<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\RoleRequest;
use App\Services\RoleService;

/**
 * @group Admin
 * @subgroup Role & Permission
 */
class RoleController extends BaseController
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->middleware('permission:roles_read')->only(['index', 'show']);
        $this->middleware('permission:roles_create')->only('store');
        $this->middleware('permission:roles_update')->only('update');
        $this->middleware('permission:roles_delete')->only('destroy');
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
        try {
            $this->roleService->deleteRole($id);
            return $this->successResponse([], 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
