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

        $moduleActions = [
            'credentials'     => ['create', 'read', 'update', 'delete'],
            'users'           => ['create', 'read', 'update', 'delete'],
            'roles'           => ['create', 'read', 'update', 'delete'],
            'settings'        => ['create', 'read', 'update', 'delete'],
            'products'        => ['create', 'read', 'update', 'delete'],
            'categories'      => ['create', 'read', 'update', 'delete'],
            'customers'       => ['create', 'read', 'update', 'delete'],
            'transactions'    => ['create', 'read', 'update', 'delete'],
            'brands'          => ['create', 'read', 'update', 'delete'],
            'locations'       => ['create', 'read', 'update', 'delete'],
            'orders'          => ['create', 'read', 'update', 'delete'],
            'coupons'         => ['create', 'read', 'update', 'delete'],
            'tickets'         => ['create', 'read', 'update', 'delete'],
            'inventory'       => ['create', 'read', 'update', 'delete'],
            'activity_logs'   => ['read', 'delete'],
            'blog_posts'      => ['create', 'read', 'update', 'delete'],
            'blog_categories' => ['create', 'read', 'update', 'delete'],
            'blog_tags'       => ['create', 'read', 'update', 'delete'],
            'blog_comments'   => ['read', 'update', 'delete'],
            'pages'           => ['create', 'read', 'update', 'delete'],
            'suppliers'       => ['create', 'read', 'update', 'delete'],
            'purchases'       => ['create', 'read', 'update', 'delete'],
            'reports'         => ['read'],
            'backups'         => ['create', 'read', 'delete'],
            'sitemap'         => ['create', 'read'],
            'campaigns'       => ['create', 'read', 'update', 'delete'],
            'stock_movements' => ['read'],
            'stock_adjustments' => ['create', 'read', 'update', 'delete'],
            'flash_sales'     => ['create', 'read', 'update', 'delete'],
            'newsletter'      => ['create', 'read', 'update', 'delete'],
            'banners'         => ['create', 'read', 'update', 'delete'],
            'faqs'            => ['create', 'read', 'update', 'delete'],
            'footer_settings' => ['read', 'update'],
            'delivery'        => ['create', 'read', 'update', 'delete'],
        ];

        $permissionsToCreate = [];
        foreach ($moduleActions as $module => $actions) {
            foreach ($actions as $action) {
                $permissionsToCreate[] = "{$module}_{$action}";
            }
        }

        // Add special overarching permissions
        $permissionsToCreate[] = 'manage_settings';
        $permissionsToCreate[] = 'manage_reviews';

        foreach (array_unique($permissionsToCreate) as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        // Generate Roles
        $superAdmin = Role::findOrCreate('super_admin', 'admin');
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // General admin gets created but permissions mapped later
        $admin = Role::findOrCreate('admin', 'admin');
         $this->command->info('Permissions seeded successfully!');
         $this->command->info('Permissions assigned to roles successfully!');
         $this->command->info('Permission seeder completed successfully!');

    }
    
}
