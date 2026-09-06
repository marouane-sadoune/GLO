<?php

namespace Tests\Feature\Api\V1;

use App\Enums\AssignmentType;
use App\Enums\HousingStatus;
use App\Enums\OccupationEndReason;
use App\Enums\RequestStatus;
use App\Models\AssignmentRequest;
use App\Models\Logement;
use App\Models\Occupant;
use App\Models\Occupation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_accepts_a_request_and_occupies_the_housing(): void
    {
        $logement = Logement::factory()->create();
        $occupant = Occupant::factory()->create();
        $request = AssignmentRequest::factory()->for($logement)->for($occupant)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$request->id}/accept", [
                'assignment_type' => AssignmentType::MANDATORY->value,
                'assignment_date' => now()->toDateString(),
                'start_date' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('data.status', RequestStatus::ACCEPTED->value)
            ->assertJsonPath('occupation.status', 'ACTIVE')
            ->assertJsonPath('occupation.logement_id', $logement->id)
            ->assertJsonCount(0, 'rival_requests');

        $this->assertSame(HousingStatus::OCCUPIED, $logement->fresh()->housing_status);
        $this->assertDatabaseHas('occupations', [
            'logement_id' => $logement->id,
            'occupant_id' => $occupant->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_accepting_returns_rival_pending_requests_without_rejecting_them(): void
    {
        $logement = Logement::factory()->create();
        $accepted = AssignmentRequest::factory()->for($logement)->create();
        $rival = AssignmentRequest::factory()->for($logement)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$accepted->id}/accept", [
                'assignment_type' => AssignmentType::MANDATORY->value,
                'assignment_date' => now()->toDateString(),
                'start_date' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonCount(1, 'rival_requests')
            ->assertJsonPath('rival_requests.0.id', $rival->id);

        $this->assertSame(RequestStatus::PENDING, $rival->fresh()->status);
    }

    public function test_department_admin_cannot_decide_a_request(): void
    {
        $request = AssignmentRequest::factory()->create();
        $admin = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$request->id}/accept", [
                'assignment_type' => AssignmentType::MANDATORY->value,
                'assignment_date' => now()->toDateString(),
                'start_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_accepting_a_request_for_an_already_occupied_housing_is_rejected(): void
    {
        $logement = Logement::factory()->create();
        $existingOccupation = Occupation::factory()->for($logement)->create();
        $request = AssignmentRequest::factory()->for($logement)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$request->id}/accept", [
                'assignment_type' => AssignmentType::MANDATORY->value,
                'assignment_date' => now()->toDateString(),
                'start_date' => now()->toDateString(),
            ])
            ->assertUnprocessable();

        $this->assertSame(RequestStatus::PENDING, $request->fresh()->status);
    }

    public function test_rejecting_a_request_leaves_the_housing_untouched(): void
    {
        $logement = Logement::factory()->create();
        $request = AssignmentRequest::factory()->for($logement)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$request->id}/reject", [
                'notes' => 'Poste déjà pourvu',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', RequestStatus::REJECTED->value);

        $this->assertSame(HousingStatus::VACANT, $logement->fresh()->housing_status);
    }

    public function test_a_rejected_request_can_be_reset_to_pending(): void
    {
        $request = AssignmentRequest::factory()->create(['status' => RequestStatus::REJECTED->value, 'decision_date' => now(), 'decided_by' => User::factory()->superAdmin()->create()->id]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$request->id}/reset")
            ->assertOk()
            ->assertJsonPath('data.status', RequestStatus::PENDING->value);
    }

    public function test_an_accepted_request_cannot_be_reset(): void
    {
        $request = AssignmentRequest::factory()->create(['status' => RequestStatus::ACCEPTED->value]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/assignment-requests/{$request->id}/reset")
            ->assertUnprocessable();
    }

    public function test_ending_an_occupation_frees_the_housing(): void
    {
        $logement = Logement::factory()->create();
        $logement->forceFill(['housing_status' => HousingStatus::OCCUPIED->value])->save();
        $occupation = Occupation::factory()->for($logement)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/occupations/{$occupation->id}/end", [
                'end_reason' => OccupationEndReason::RETIREMENT->value,
                'end_date' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ENDED')
            ->assertJsonPath('data.end_reason', OccupationEndReason::RETIREMENT->value);

        $this->assertSame(HousingStatus::VACANT, $logement->fresh()->housing_status);
    }

    public function test_ending_an_already_ended_occupation_is_rejected(): void
    {
        $occupation = Occupation::factory()->create([
            'status' => 'ENDED',
            'end_date' => now()->subDay()->toDateString(),
            'end_reason' => OccupationEndReason::OTHER->value,
        ]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->postJson("/api/v1/occupations/{$occupation->id}/end", [
                'end_reason' => OccupationEndReason::RETIREMENT->value,
                'end_date' => now()->toDateString(),
            ])
            ->assertUnprocessable();
    }
}
