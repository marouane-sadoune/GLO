<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@glo.ma');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin GLO',
                'password' => Hash::make($password),
                'active' => true,
            ]
        );

        $user->assignRole(UserRole::SUPER_ADMIN->value);

        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $department = Department::query()->firstOrFail();
        $establishment = Establishment::firstOrCreate(
            ['code' => 'DEMO-OUJDA'],
            [
                'department_id' => $department->id,
                'name_fr' => 'Établissement de démonstration',
                'name_ar' => 'مؤسسة تجريبية',
                'type' => 'SCHOOL',
                'address' => 'Oujda',
            ],
        );

        $departmentAdmin = User::updateOrCreate(
            ['email' => 'department.admin@glo.ma'],
            [
                'name' => 'Department Admin Demo',
                'password' => Hash::make('password'),
                'department_id' => $department->id,
                'establishment_id' => null,
                'active' => true,
            ],
        );
        $departmentAdmin->syncRoles([UserRole::DEPARTMENT_ADMIN->value]);

        $establishmentManager = User::updateOrCreate(
            ['email' => 'establishment.manager@glo.ma'],
            [
                'name' => 'Establishment Manager Demo',
                'password' => Hash::make('password'),
                'department_id' => $department->id,
                'establishment_id' => $establishment->id,
                'active' => true,
            ],
        );
        $establishmentManager->syncRoles([UserRole::ESTABLISHMENT_MANAGER->value]);
    }
}
