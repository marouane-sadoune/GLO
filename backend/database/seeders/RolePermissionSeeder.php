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
            'requests.verify',
            'requests.approve',
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

        // 2. DP Agent role (province-scoped: submits, never decides)
        $dpAgent = Role::firstOrCreate(['name' => UserRole::DP_AGENT->value, 'guard_name' => 'web']);
        $dpAgent->syncPermissions([
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

        // 3. Establishment Manager role (single-establishment data entry)
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

        // 4. AREF Validator role (region-wide: reviews dossiers, verifies or rejects)
        $arefValidator = Role::firstOrCreate(['name' => UserRole::AREF_VALIDATOR->value, 'guard_name' => 'web']);
        $arefValidator->syncPermissions([
            'departments.view',
            'establishments.view',
            'logements.view',
            'occupants.view',
            'requests.view',
            'requests.verify',
            'occupations.view',
            'documents.view',
            'vacations.view',
            'history.view',
            'stats.view',
            'exports.run',
        ]);

        // 5. AREF Director role (region-wide: final approval + signed PDF)
        $arefDirector = Role::firstOrCreate(['name' => UserRole::AREF_DIRECTOR->value, 'guard_name' => 'web']);
        $arefDirector->syncPermissions([
            'departments.view',
            'establishments.view',
            'logements.view',
            'occupants.view',
            'requests.view',
            'requests.approve',
            'occupations.view',
            'documents.view',
            'vacations.view',
            'history.view',
            'stats.view',
            'exports.run',
        ]);
    }
}
