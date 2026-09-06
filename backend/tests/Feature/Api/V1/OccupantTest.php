<?php

namespace Tests\Feature\Api\V1;

use App\Models\AssignmentRequest;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\Logement;
use App\Models\Occupant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccupantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_establishment_manager_can_create_an_occupant_anchored_to_their_establishment(): void
    {
        $establishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/occupants', [
                'establishment_id' => $establishment->id,
                'first_name_fr' => 'Ahmed',
                'last_name_fr' => 'Benali',
            ])
            ->assertCreated()
            ->assertJsonPath('data.full_name_fr', 'Ahmed Benali');
    }

    public function test_establishment_manager_cannot_anchor_an_occupant_to_another_establishment(): void
    {
        $ownEstablishment = Establishment::factory()->create();
        $otherEstablishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($ownEstablishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/occupants', [
                'establishment_id' => $otherEstablishment->id,
                'first_name_fr' => 'Ahmed',
                'last_name_fr' => 'Benali',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['establishment_id']);
    }

    public function test_an_occupant_with_no_occupation_yet_is_visible_to_its_anchor_establishment_manager(): void
    {
        $establishment = Establishment::factory()->create();
        $occupant = Occupant::factory()->for($establishment)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->getJson("/api/v1/occupants/{$occupant->id}")
            ->assertOk();
    }

    public function test_an_occupant_is_visible_via_a_pending_request_even_outside_their_anchor_establishment(): void
    {
        $department = Department::factory()->create();
        $anchorEstablishment = Establishment::factory()->for($department)->create();
        $housingEstablishment = Establishment::factory()->for($department)->create();

        $occupant = Occupant::factory()->for($anchorEstablishment)->create();
        $logement = Logement::factory()->for($housingEstablishment)->create();
        AssignmentRequest::factory()->for($logement)->for($occupant)->create([
            'submitted_at' => now()->toDateString(),
        ]);

        $manager = User::factory()->establishmentManager($housingEstablishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->getJson("/api/v1/occupants/{$occupant->id}")
            ->assertOk();
    }

    public function test_department_admin_cannot_view_an_occupant_outside_their_department(): void
    {
        $occupant = Occupant::factory()->create();
        $admin = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson("/api/v1/occupants/{$occupant->id}")
            ->assertNotFound();
    }

    public function test_establishment_manager_cannot_delete_an_occupant(): void
    {
        $establishment = Establishment::factory()->create();
        $occupant = Occupant::factory()->for($establishment)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->deleteJson("/api/v1/occupants/{$occupant->id}")
            ->assertForbidden();
    }
}
