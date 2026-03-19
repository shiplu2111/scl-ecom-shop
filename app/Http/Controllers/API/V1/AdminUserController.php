<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AdminUserRequest;
use Illuminate\Http\Request;

/**
 * @group Admin
 */
class AdminUserController extends BaseController
{
    public function index()
    {
        $admins = Admin::with('roles')->get();
        return $this->successResponse($admins, 'Admins retrieved successfully');
    }

    public function store(AdminUserRequest $request)
    {
        $data = $request->validated();
        
        $admin = new Admin();
        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->password = Hash::make($data['password']);
        $admin->is_active = $data['is_active'] ?? true;
        $admin->save();

        $admin->syncRoles([$data['role']]);

        return $this->successResponse($admin->load('roles'), 'Admin user created successfully', 201);
    }

    public function show($id)
    {
        $admin = Admin::with('roles')->findOrFail($id);
        return $this->successResponse($admin, 'Admin user retrieved successfully');
    }

    public function update(AdminUserRequest $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $data = $request->validated();

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        if (isset($data['is_active'])) {
             $admin->is_active = $data['is_active'];
        }

        $admin->save();
        $admin->syncRoles([$data['role']]);

        return $this->successResponse($admin->load('roles'), 'Admin user updated successfully');
    }

    public function destroy($id)
    {
        // Prevent super_admin from deleting themselves if needed, but for now standard destroy
        $admin = Admin::findOrFail($id);
        $admin->delete();
        
        return $this->successResponse([], 'Admin user deleted successfully');
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);
        $admin = Admin::findOrFail($id);
        $admin->is_active = $request->is_active;
        $admin->save();

        return $this->successResponse($admin, 'Admin status updated successfully');
    }
}
