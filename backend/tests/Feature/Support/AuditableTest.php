<?php

namespace Tests\Feature\Support;

use App\Enums\AuditAction;
use App\Models\HousingHistory;
use App\Models\Logement;
use App\Models\Occupation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_creating_a_logement_writes_a_created_audit_row(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAsUser($admin);

        $logement = Logement::factory()->create();

        $history = HousingHistory::where('auditable_type', $logement->getMorphClass())
            ->where('auditable_id', $logement->id)
            ->sole();

        $this->assertSame(AuditAction::CREATED, $history->action);
        $this->assertSame($logement->id, $history->logement_id);
        $this->assertSame($admin->id, $history->user_id);
        $this->assertNull($history->old_values);
        $this->assertSame($logement->inventory_number, $history->new_values['inventory_number']);
    }

    public function test_updating_a_logement_logs_only_the_changed_fields(): void
    {
        $logement = Logement::factory()->create(['notes' => 'old']);

        $logement->update(['notes' => 'new']);

        $history = HousingHistory::where('auditable_type', $logement->getMorphClass())
            ->where('auditable_id', $logement->id)
            ->where('action', AuditAction::UPDATED->value)
            ->sole();

        $this->assertSame(['notes' => 'old'], $history->old_values);
        $this->assertSame(['notes' => 'new'], $history->new_values);
    }

    public function test_saving_with_no_real_changes_does_not_log_an_update(): void
    {
        $logement = Logement::factory()->create();

        $logement->save();

        $this->assertSame(0, HousingHistory::where('auditable_type', $logement->getMorphClass())
            ->where('auditable_id', $logement->id)
            ->where('action', AuditAction::UPDATED->value)
            ->count());
    }

    public function test_deleting_a_logement_logs_a_deleted_row_with_the_prior_state(): void
    {
        $logement = Logement::factory()->create();
        $id = $logement->id;

        $logement->delete();

        $history = HousingHistory::where('auditable_type', $logement->getMorphClass())
            ->where('auditable_id', $id)
            ->where('action', AuditAction::DELETED->value)
            ->sole();

        $this->assertSame($id, $history->old_values['id']);
    }

    public function test_occupation_audit_rows_derive_their_logement_id(): void
    {
        $logement = Logement::factory()->create();
        $occupation = Occupation::factory()->for($logement)->create();

        $history = HousingHistory::where('auditable_type', $occupation->getMorphClass())
            ->where('auditable_id', $occupation->id)
            ->sole();

        $this->assertSame($logement->id, $history->logement_id);
    }
}
