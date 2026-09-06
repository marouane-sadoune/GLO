<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\HousingStatus;
use App\Enums\OccupationEndReason;
use App\Enums\OccupationStatus;
use App\Models\AssignmentRequest;
use App\Models\Logement;
use App\Models\Occupant;
use App\Models\Occupation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Starts and ends occupations. logement.housing_status is derived data
 * (AMB-02): this is the only place allowed to write it, via forceFill,
 * inside the same transaction that creates/ends the occupation.
 */
class OccupationService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * @param  array{assignment_date: string, assignment_type: string, start_date: string, notes?: string|null}  $attributes
     */
    public function start(
        Logement $logement,
        Occupant $occupant,
        array $attributes,
        ?AssignmentRequest $request = null,
    ): Occupation {
        return DB::transaction(function () use ($logement, $occupant, $attributes, $request) {
            $logement = Logement::whereKey($logement->id)->lockForUpdate()->firstOrFail();

            if ($logement->activeOccupation()->exists()) {
                throw ValidationException::withMessages([
                    'logement_id' => ['Ce logement est déjà occupé.'],
                ]);
            }

            if ($occupant->activeOccupation()->exists()) {
                throw ValidationException::withMessages([
                    'occupant_id' => ['Cet occupant occupe déjà un autre logement.'],
                ]);
            }

            try {
                $occupation = Model::withoutEvents(fn () => Occupation::create([
                    'logement_id' => $logement->id,
                    'occupant_id' => $occupant->id,
                    'assignment_request_id' => $request?->id,
                    'assignment_date' => $attributes['assignment_date'],
                    'assignment_type' => $attributes['assignment_type'],
                    'start_date' => $attributes['start_date'],
                    'status' => OccupationStatus::ACTIVE->value,
                    'notes' => $attributes['notes'] ?? null,
                ]));
            } catch (QueryException) {
                // Backstop for the generated-column unique indexes (docs/ARCHITECTURE.md §3.6):
                // a concurrent request slipped past the checks above.
                throw ValidationException::withMessages([
                    'logement_id' => ['Ce logement ou cet occupant possède déjà une occupation active.'],
                ]);
            }

            Model::withoutEvents(fn () => $logement->forceFill([
                'housing_status' => HousingStatus::OCCUPIED->value,
            ])->save());

            $this->auditLogger->log(
                action: AuditAction::OCCUPATION_STARTED,
                subject: $occupation,
                newValues: $occupation->getAttributes(),
                logementId: $logement->id,
            );

            return $occupation;
        });
    }

    public function end(
        Occupation $occupation,
        OccupationEndReason $reason,
        string $endDate,
        ?string $notes = null,
    ): Occupation {
        return DB::transaction(function () use ($occupation, $reason, $endDate, $notes) {
            $occupation = Occupation::whereKey($occupation->id)->lockForUpdate()->firstOrFail();

            if ($occupation->status !== OccupationStatus::ACTIVE) {
                throw ValidationException::withMessages([
                    'status' => ["Cette occupation n'est plus active."],
                ]);
            }

            $old = $occupation->only(['status', 'end_date', 'end_reason']);

            Model::withoutEvents(fn () => $occupation->forceFill([
                'status' => OccupationStatus::ENDED->value,
                'end_date' => $endDate,
                'end_reason' => $reason->value,
                'notes' => $notes ?? $occupation->notes,
            ])->save());

            $logement = Logement::whereKey($occupation->logement_id)->lockForUpdate()->firstOrFail();
            Model::withoutEvents(fn () => $logement->forceFill([
                'housing_status' => HousingStatus::VACANT->value,
            ])->save());

            $this->auditLogger->log(
                action: AuditAction::OCCUPATION_ENDED,
                subject: $occupation,
                oldValues: $old,
                newValues: $occupation->only(['status', 'end_date', 'end_reason']),
                logementId: $occupation->logement_id,
            );

            return $occupation;
        });
    }
}
