<?php

namespace Tests\Feature\Api\V1;

use App\Enums\HousingStatus;
use App\Models\Establishment;
use App\Models\Logement;
use App\Models\Occupation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_recording_a_vacation_ends_the_occupation_and_frees_the_housing(): void
    {
        $establishment = Establishment::factory()->create();
        $logement = Logement::factory()->for($establishment)->create();
        $logement->forceFill(['housing_status' => HousingStatus::OCCUPIED->value])->save();
        $occupation = Occupation::factory()->for($logement)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/vacations', [
                'occupation_id' => $occupation->id,
                'vacation_date' => now()->toDateString(),
                'reason' => 'Fin de fonction',
                'legal_basis' => 'Décision n°42',
            ])
            ->assertCreated()
            ->assertJsonPath('data.occupation_id', $occupation->id);

        $this->assertSame('ENDED', $occupation->fresh()->status->value);
        $this->assertSame(HousingStatus::VACANT, $logement->fresh()->housing_status);
        $this->assertDatabaseHas('vacations', ['occupation_id' => $occupation->id]);
    }

    public function test_establishment_manager_cannot_record_a_vacation_outside_their_establishment(): void
    {
        $occupation = Occupation::factory()->create();
        $manager = User::factory()->establishmentManager()->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->postJson('/api/v1/vacations', [
                'occupation_id' => $occupation->id,
                'vacation_date' => now()->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['occupation_id']);
    }

    public function test_recording_a_vacation_for_an_already_ended_occupation_is_rejected(): void
    {
        $occupation = Occupation::factory()->create([
            'status' => 'ENDED',
            'end_date' => now()->subDay()->toDateString(),
            'end_reason' => 'RETIREMENT',
        ]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson('/api/v1/vacations', [
                'occupation_id' => $occupation->id,
                'vacation_date' => now()->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['occupation_id']);
    }
}
