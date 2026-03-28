<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\PermissionRequest;
use App\Services\PermissionService;

/**
 * @group Admin
 * @subgroup Role & Permission
 */
class PermissionController extends BaseController
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('permission:roles_read')->only(['index', 'show']);
        $this->middleware('permission:roles_create')->only('store');
        $this->middleware('permission:roles_update')->only('update');
        $this->middleware('permission:roles_delete')->only('destroy');
    }

    public function index()
    {
        $permissions = $this->permissionService->getAllAdminPermissions();
        return $this->successResponse($permissions, 'Permissions fetched successfully');
    }

    public function store(PermissionRequest $request)
    {
        $permission = $this->permissionService->createPermission($request->validated());
        return $this->successResponse($permission, 'Permission created successfully', 201);
    }

    public function show($id)
    {
        $permission = $this->permissionService->find($id);
        return $this->successResponse($permission, 'Permission fetched successfully');
    }

    public function update(PermissionRequest $request, $id)
    {
        $permission = $this->permissionService->updatePermission($id, $request->validated());
        return $this->successResponse($permission, 'Permission updated successfully');
    }

    public function destroy($id)
    {
        $this->permissionService->deletePermission($id);
        return $this->successResponse([], 'Permission deleted successfully');
    }
}
