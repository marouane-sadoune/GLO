<?php

namespace Tests\Feature\Api\V1;

use App\Models\Department;
use App\Models\Establishment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_lists_every_department(): void
    {
        Department::factory()->count(3)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson('/api/v1/departments')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_department_admin_only_sees_their_own_department(): void
    {
        $ownDepartment = Department::factory()->create();
        Department::factory()->create();
        $admin = User::factory()->departmentAdmin($ownDepartment)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson('/api/v1/departments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownDepartment->id);
    }

    public function test_department_admin_cannot_create_a_department(): void
    {
        $admin = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/departments', [
                'code' => '999',
                'name_fr' => 'TEST',
                'name_ar' => 'تجربة',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_department(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/departments', [
                'code' => '999',
                'name_fr' => 'TEST',
                'name_ar' => 'تجربة',
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', '999');
    }

    public function test_duplicate_department_code_is_rejected(): void
    {
        Department::factory()->create(['code' => '381']);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/departments', [
                'code' => '381',
                'name_fr' => 'TEST',
                'name_ar' => 'تجربة',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_deleting_a_department_with_establishments_returns_a_conflict(): void
    {
        $department = Department::factory()->create();
        Establishment::factory()->for($department)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->deleteJson("/api/v1/departments/{$department->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_department_admin_cannot_view_another_departments_details(): void
    {
        $otherDepartment = Department::factory()->create();
        $admin = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson("/api/v1/departments/{$otherDepartment->id}")
            ->assertNotFound();
    }
}
