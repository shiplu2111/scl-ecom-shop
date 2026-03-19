<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'credentials',
            'users',
            'roles',
            'permissions',
            'settings',
            'products',
            'categories',
            'brands',
            'locations',
            'orders',
            'coupons',
            'inventory',
            'activity_logs',
            'blog_posts',
            'blog_categories',
            'blog_tags',
            'pages'
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        $permissionsToCreate = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionsToCreate[] = "{$module}_{$action}";
            }
        }

        // Add special overarching permissions if necessary (e.g. manage_settings, etc)
        $permissionsToCreate[] = 'manage_settings';
        $permissionsToCreate[] = 'dashboard_read';

        foreach (array_unique($permissionsToCreate) as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        // Generate Roles
        $superAdmin = Role::findOrCreate('super_admin', 'admin');
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // General admin gets created but permissions mapped later
        $admin = Role::findOrCreate('admin', 'admin');
    }
}
