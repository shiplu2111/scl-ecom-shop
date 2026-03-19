<?php

namespace App\Http\Controllers\API\V1;

use App\Repositories\AdminRepositoryInterface;
use Illuminate\Http\Request;

/**
 * @group Admin
 */
class AdminRoleController extends BaseController
{
    protected AdminRepositoryInterface $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function assignRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name'
        ]);

        $admin = $this->adminRepository->find($id);
        $admin->syncRoles($request->roles);

        $admin->load('roles', 'permissions');

        return $this->successResponse($admin, 'Roles synced to admin successfully');
    }
}
