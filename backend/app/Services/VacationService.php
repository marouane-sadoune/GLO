<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\OccupationEndReason;
use App\Models\Occupation;
use App\Models\Vacation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Vacation is one of several ways an occupation ends (AMB-08): recording one
 * always ends the occupation through OccupationService first, then adds the
 * vacations row with the legal basis. A mutation, retirement or death ends
 * an occupation the same way, just without a vacation record.
 */
class VacationService
{
    public function __construct(
        private readonly OccupationService $occupationService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function record(
        Occupation $occupation,
        string $vacationDate,
        ?string $reason = null,
        ?string $legalBasis = null,
        ?string $notes = null,
    ): Vacation {
        return DB::transaction(function () use ($occupation, $vacationDate, $reason, $legalBasis, $notes) {
            $occupation = $this->occupationService->end(
                $occupation,
                OccupationEndReason::VACATION,
                $vacationDate,
                $notes,
            );

            $vacation = Model::withoutEvents(fn () => Vacation::create([
                'logement_id' => $occupation->logement_id,
                'occupation_id' => $occupation->id,
                'occupant_id' => $occupation->occupant_id,
                'vacation_date' => $vacationDate,
                'reason' => $reason,
                'legal_basis' => $legalBasis,
                'notes' => $notes,
            ]));

            $this->auditLogger->log(
                action: AuditAction::VACATION_RECORDED,
                subject: $vacation,
                newValues: $vacation->getAttributes(),
                logementId: $occupation->logement_id,
            );

            return $vacation;
        });
    }
}
