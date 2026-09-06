<?php

namespace Tests\Feature\Api\V1;

use App\Models\Department;
use App\Models\Establishment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablishmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_department_admin_can_create_an_establishment_in_their_own_department(): void
    {
        $department = Department::factory()->create();
        $admin = User::factory()->departmentAdmin($department)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/establishments', [
                'department_id' => $department->id,
                'code' => 'ETB-01',
                'name_fr' => 'Lycée Ibn Khaldoun',
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'ETB-01');
    }

    public function test_department_admin_cannot_create_an_establishment_in_another_department(): void
    {
        $ownDepartment = Department::factory()->create();
        $otherDepartment = Department::factory()->create();
        $admin = User::factory()->departmentAdmin($ownDepartment)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/establishments', [
                'department_id' => $otherDepartment->id,
                'code' => 'ETB-02',
                'name_fr' => 'Lycée Ibn Khaldoun',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department_id']);
    }

    public function test_establishment_manager_cannot_create_an_establishment(): void
    {
        $establishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/establishments', [
                'department_id' => $establishment->department_id,
                'code' => 'ETB-03',
                'name_fr' => 'Lycée Ibn Khaldoun',
            ])
            ->assertForbidden();
    }

    public function test_duplicate_code_within_the_same_department_is_rejected(): void
    {
        $department = Department::factory()->create();
        Establishment::factory()->for($department)->create(['code' => 'DUP']);
        $admin = User::factory()->departmentAdmin($department)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/establishments', [
                'department_id' => $department->id,
                'code' => 'DUP',
                'name_fr' => 'Lycée Ibn Khaldoun',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_the_same_code_is_allowed_in_a_different_department(): void
    {
        $departmentA = Department::factory()->create();
        $departmentB = Department::factory()->create();
        Establishment::factory()->for($departmentA)->create(['code' => 'SHARED']);

        $admin = User::factory()->departmentAdmin($departmentB)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/establishments', [
                'department_id' => $departmentB->id,
                'code' => 'SHARED',
                'name_fr' => 'Lycée Ibn Khaldoun',
            ])
            ->assertCreated();
    }

    public function test_establishment_manager_only_sees_their_own_establishment(): void
    {
        $department = Department::factory()->create();
        $ownEstablishment = Establishment::factory()->for($department)->create();
        Establishment::factory()->for($department)->create();

        $manager = User::factory()->establishmentManager($ownEstablishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->getJson('/api/v1/establishments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownEstablishment->id);
    }

    public function test_establishment_manager_cannot_delete_their_own_establishment(): void
    {
        $establishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->deleteJson("/api/v1/establishments/{$establishment->id}")
            ->assertForbidden();
    }

    public function test_super_admin_can_delete_an_empty_establishment(): void
    {
        $establishment = Establishment::factory()->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->deleteJson("/api/v1/establishments/{$establishment->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('establishments', ['id' => $establishment->id]);
    }
}
