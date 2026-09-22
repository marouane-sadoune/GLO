<?php

namespace Tests\Feature\Api\V1;

use App\Models\Department;
use App\Models\Establishment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_an_establishment_manager(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $establishment = Establishment::factory()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/users', [
                'name' => 'New Manager',
                'email' => 'new.manager@glo.ma',
                'password' => 'password123',
                'role' => 'ESTABLISHMENT_MANAGER',
                'department_id' => $establishment->department_id,
                'establishment_id' => $establishment->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'ESTABLISHMENT_MANAGER')
            ->assertJsonPath('data.establishment_id', $establishment->id);

        $this->assertTrue(User::where('email', 'new.manager@glo.ma')->first()->hasRole('ESTABLISHMENT_MANAGER'));
    }

    public function test_establishment_manager_cannot_manage_users(): void
    {
        $manager = User::factory()->establishmentManager()->create();
        $establishment = Establishment::factory()->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/users', [
                'name' => 'Someone',
                'email' => 'someone@glo.ma',
                'password' => 'password123',
                'role' => 'ESTABLISHMENT_MANAGER',
                'department_id' => $establishment->department_id,
                'establishment_id' => $establishment->id,
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_a_user_role_without_resetting_the_password_when_left_blank(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $department = Department::factory()->create();
        $target = User::factory()->departmentAdmin($department)->create();
        $originalPassword = $target->password;

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->putJson("/api/v1/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'DEPARTMENT_ADMIN',
                'department_id' => $department->id,
                'active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.active', false);

        $this->assertSame($originalPassword, $target->fresh()->password);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->deleteJson("/api/v1/users/{$admin->id}")
            ->assertForbidden();
    }
}
