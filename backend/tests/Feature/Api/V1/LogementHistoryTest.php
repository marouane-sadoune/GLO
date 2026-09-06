<?php

namespace Tests\Feature\Api\V1;

use App\Models\Establishment;
use App\Models\Logement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogementHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_it_lists_the_audit_trail_for_a_logement(): void
    {
        $establishment = Establishment::factory()->create();
        $manager = User::factory()->establishmentManager($establishment)->create();
        $this->actingAsUser($manager);

        $logement = Logement::factory()->for($establishment)->create();
        $logement->update(['notes' => 'Peinture refaite']);

        $this->fromFrontend()
            ->getJson("/api/v1/logements/{$logement->id}/history")
            ->assertOk()
            ->assertJsonPath('data.0.action', 'UPDATED')
            ->assertJsonPath('data.1.action', 'CREATED');
    }

    public function test_department_admin_cannot_view_history_for_a_logement_outside_their_department(): void
    {
        $logement = Logement::factory()->create();
        $admin = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->getJson("/api/v1/logements/{$logement->id}/history")
            ->assertNotFound();
    }
}
