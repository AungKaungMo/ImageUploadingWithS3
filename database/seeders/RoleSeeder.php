<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'developer',
            'super admin',
            'admin',
            'officers'
        ];

        $permissionList = Permission::all();

        foreach ($roles as $role) {
            $roleInstance = Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'api'
            ]);
            $roleInstance->syncPermissions($permissionList);
        }
    }
}
