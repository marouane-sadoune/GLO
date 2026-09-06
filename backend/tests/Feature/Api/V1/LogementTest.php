<?php

namespace Tests\Feature\Api\V1;

use App\Enums\HousingCategory;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\Logement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_lists_every_logement(): void
    {
        Logement::factory()->count(3)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson('/api/v1/logements')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_department_admin_only_lists_logements_in_their_department(): void
    {
        $departmentA = Department::factory()->create();
        $establishmentA = Establishment::factory()->for($departmentA)->create();
        Logement::factory()->for($establishmentA)->create();
        Logement::factory()->create();

        $admin = User::factory()->departmentAdmin($departmentA)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson('/api/v1/logements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_department_admin_cannot_view_a_logement_outside_their_department(): void
    {
        $logement = Logement::factory()->create();
        $admin = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson("/api/v1/logements/{$logement->id}")
            ->assertNotFound();
    }

    public function test_establishment_manager_can_create_a_logement_in_their_own_establishment(): void
    {
        $establishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/logements', [
                'establishment_id' => $establishment->id,
                'inventory_number' => 'INV-001',
                'location_fr' => 'Rue des Fleurs',
                'housing_category' => HousingCategory::ADMINISTRATIVE->value,
            ])
            ->assertCreated()
            ->assertJsonPath('data.inventory_number', 'INV-001')
            ->assertJsonPath('data.housing_status', 'VACANT');

        $this->assertDatabaseHas('logements', [
            'establishment_id' => $establishment->id,
            'inventory_number' => 'INV-001',
        ]);
    }

    public function test_establishment_manager_cannot_create_a_logement_in_another_establishment(): void
    {
        $ownEstablishment = Establishment::factory()->create();
        $otherEstablishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($ownEstablishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/logements', [
                'establishment_id' => $otherEstablishment->id,
                'inventory_number' => 'INV-002',
                'location_fr' => 'Rue des Fleurs',
                'housing_category' => HousingCategory::ADMINISTRATIVE->value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['establishment_id']);
    }

    public function test_housing_status_cannot_be_set_directly(): void
    {
        $establishment = Establishment::factory()->create();
        $admin = User::factory()->departmentAdmin($establishment->department)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/logements', [
                'establishment_id' => $establishment->id,
                'inventory_number' => 'INV-003',
                'location_fr' => 'Rue des Fleurs',
                'housing_category' => HousingCategory::ADMINISTRATIVE->value,
                'housing_status' => 'OCCUPIED',
            ])
            ->assertCreated()
            ->assertJsonPath('data.housing_status', 'VACANT');
    }

    public function test_duplicate_inventory_number_in_the_same_establishment_is_rejected(): void
    {
        $establishment = Establishment::factory()->create();
        Logement::factory()->for($establishment)->create(['inventory_number' => 'INV-100']);
        $admin = User::factory()->departmentAdmin($establishment->department)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/logements', [
                'establishment_id' => $establishment->id,
                'inventory_number' => 'INV-100',
                'location_fr' => 'Rue des Fleurs',
                'housing_category' => HousingCategory::ADMINISTRATIVE->value,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['inventory_number']);
    }

    public function test_establishment_manager_cannot_delete_a_logement(): void
    {
        $establishment = Establishment::factory()->create();
        $logement = Logement::factory()->for($establishment)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->deleteJson("/api/v1/logements/{$logement->id}")
            ->assertForbidden();
    }

    public function test_department_admin_can_delete_a_logement_in_their_department(): void
    {
        $department = Department::factory()->create();
        $establishment = Establishment::factory()->for($department)->create();
        $logement = Logement::factory()->for($establishment)->create();
        $admin = User::factory()->departmentAdmin($department)->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->deleteJson("/api/v1/logements/{$logement->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('logements', ['id' => $logement->id]);
    }
}
