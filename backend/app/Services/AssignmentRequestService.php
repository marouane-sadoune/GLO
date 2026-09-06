<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\RequestStatus;
use App\Models\AssignmentRequest;
use App\Models\Occupation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentRequestService
{
    public function __construct(
        private readonly OccupationService $occupationService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * Accepting settles this request and starts the occupation, but per AMB-05
     * never silently rejects rival PENDING requests on the same housing — they
     * come back for the caller to reject explicitly in a second call.
     *
     * @param  array{assignment_date: string, assignment_type: string, start_date: string, notes?: string|null}  $occupationAttributes
     * @return array{request: AssignmentRequest, occupation: Occupation, rival_requests: Collection<int, AssignmentRequest>}
     */
    public function accept(AssignmentRequest $request, User $decider, array $occupationAttributes): array
    {
        return DB::transaction(function () use ($request, $decider, $occupationAttributes) {
            $request = AssignmentRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== RequestStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => ['Cette demande a déjà été traitée.'],
                ]);
            }

            $occupation = $this->occupationService->start(
                $request->logement,
                $request->occupant,
                $occupationAttributes,
                $request,
            );

            Model::withoutEvents(fn () => $request->forceFill([
                'status' => RequestStatus::ACCEPTED->value,
                'decision_date' => now()->toDateString(),
                'decided_by' => $decider->id,
            ])->save());

            $this->auditLogger->log(
                action: AuditAction::REQUEST_ACCEPTED,
                subject: $request,
                newValues: $request->only(['status', 'decision_date', 'decided_by']),
                logementId: $request->logement_id,
            );

            $rivals = AssignmentRequest::where('logement_id', $request->logement_id)
                ->where('id', '!=', $request->id)
                ->where('status', RequestStatus::PENDING->value)
                ->get();

            return [
                'request' => $request,
                'occupation' => $occupation,
                'rival_requests' => $rivals,
            ];
        });
    }

    public function reject(AssignmentRequest $request, User $decider, ?string $notes = null): AssignmentRequest
    {
        return DB::transaction(function () use ($request, $decider, $notes) {
            $request = AssignmentRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== RequestStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => ['Cette demande a déjà été traitée.'],
                ]);
            }

            Model::withoutEvents(fn () => $request->forceFill([
                'status' => RequestStatus::REJECTED->value,
                'decision_date' => now()->toDateString(),
                'decided_by' => $decider->id,
                'notes' => $notes ?? $request->notes,
            ])->save());

            $this->auditLogger->log(
                action: AuditAction::REQUEST_REJECTED,
                subject: $request,
                newValues: $request->only(['status', 'decision_date', 'decided_by', 'notes']),
                logementId: $request->logement_id,
            );

            return $request;
        });
    }

    /**
     * Reverts a REJECTED request back to PENDING so it can be reconsidered.
     * An ACCEPTED request cannot be reset this way: undoing it means ending
     * the occupation it created, which goes through OccupationService::end().
     */
    public function reset(AssignmentRequest $request): AssignmentRequest
    {
        return DB::transaction(function () use ($request) {
            $request = AssignmentRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== RequestStatus::REJECTED) {
                throw ValidationException::withMessages([
                    'status' => ['Seule une demande rejetée peut être réinitialisée.'],
                ]);
            }

            Model::withoutEvents(fn () => $request->forceFill([
                'status' => RequestStatus::PENDING->value,
                'decision_date' => null,
                'decided_by' => null,
            ])->save());

            $this->auditLogger->log(
                action: AuditAction::REQUEST_RESET,
                subject: $request,
                newValues: $request->only(['status']),
                logementId: $request->logement_id,
            );

            return $request;
        });
    }
}
