<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const ROLES = [
        'super_admin',
        'developer',
        'demo_super_admin',
        'branch_admin',
        'patient',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            if ($roleName === 'demo_super_admin') {
                $viewPermissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'view_%')->get();
                $role->syncPermissions($viewPermissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
