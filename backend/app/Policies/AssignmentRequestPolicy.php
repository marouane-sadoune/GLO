<?php

namespace App\Policies;

use App\Models\AssignmentRequest;
use App\Models\User;

class AssignmentRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('requests.view');
    }

    public function view(User $user, AssignmentRequest $request): bool
    {
        return $user->can('requests.view') && $this->inScope($user, $request);
    }

    public function create(User $user): bool
    {
        return $user->can('requests.create');
    }

    /**
     * First review stage: AREF_VALIDATOR checks a PENDING dossier and marks it
     * VERIFIED, or rejects it. Plain permission check, no scope clause — like
     * the final approval below, this is a genuinely region-wide capability.
     */
    public function verify(User $user): bool
    {
        return $user->can('requests.verify');
    }

    /**
     * Final sign-off: AREF_DIRECTOR accepts a VERIFIED dossier (creating the
     * occupation) or rejects it.
     */
    public function approve(User $user): bool
    {
        return $user->can('requests.approve');
    }

    /**
     * Reject/reset span both review stages, so either permission qualifies —
     * the service layer enforces which status transitions are actually valid.
     */
    public function decide(User $user): bool
    {
        return $user->can('requests.verify') || $user->can('requests.approve');
    }

    private function inScope(User $user, AssignmentRequest $request): bool
    {
        return AssignmentRequest::visibleTo($user)->whereKey($request->id)->exists();
    }
}
