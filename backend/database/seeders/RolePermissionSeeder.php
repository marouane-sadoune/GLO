<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'departments.view',
            'departments.manage',
            'establishments.view',
            'establishments.create',
            'establishments.update',
            'establishments.delete',
            'logements.view',
            'logements.create',
            'logements.update',
            'logements.delete',
            'occupants.view',
            'occupants.create',
            'occupants.update',
            'occupants.delete',
            'requests.view',
            'requests.create',
            'requests.decide',
            'occupations.view',
            'occupations.manage',
            'documents.view',
            'documents.upload',
            'documents.delete',
            'vacations.view',
            'vacations.create',
            'history.view',
            'users.view',
            'users.manage',
            'stats.view',
            'exports.run',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 1. Super Admin role gets all permissions
        $superAdmin = Role::firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Department Admin role
        $deptAdmin = Role::firstOrCreate(['name' => UserRole::DEPARTMENT_ADMIN->value, 'guard_name' => 'web']);
        $deptAdmin->syncPermissions([
            'departments.view',
            'establishments.view',
            'establishments.create',
            'establishments.update',
            'logements.view',
            'logements.create',
            'logements.update',
            'logements.delete',
            'occupants.view',
            'occupants.create',
            'occupants.update',
            'requests.view',
            'requests.create',
            'occupations.view',
            'occupations.manage',
            'documents.view',
            'documents.upload',
            'documents.delete',
            'vacations.view',
            'vacations.create',
            'history.view',
            'stats.view',
            'exports.run',
        ]);

        // 3. Establishment Manager role
        $estManager = Role::firstOrCreate(['name' => UserRole::ESTABLISHMENT_MANAGER->value, 'guard_name' => 'web']);
        $estManager->syncPermissions([
            'departments.view',
            'establishments.view',
            'logements.view',
            'logements.create',
            'logements.update',
            'occupants.view',
            'occupants.create',
            'occupants.update',
            'requests.view',
            'requests.create',
            'occupations.view',
            'occupations.manage',
            'documents.view',
            'documents.upload',
            'vacations.view',
            'vacations.create',
            'history.view',
            'stats.view',
            'exports.run',
        ]);
    }
}
