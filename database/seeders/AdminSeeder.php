<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password', // will be hashed by model cast
                'is_active' => true,
            ]
        );

        $role = Role::findOrCreate('super_admin', 'admin');
        
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole($role);
        }
    }
}
