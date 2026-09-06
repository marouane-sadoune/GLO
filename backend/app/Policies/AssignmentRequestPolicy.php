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
     * Accept/reject/reset. Deliberately a plain permission check with no scope
     * clause: requests.decide is granted to SUPER_ADMIN alone, the one
     * genuinely region-wide capability in the system (docs/ARCHITECTURE.md §8).
     */
    public function decide(User $user): bool
    {
        return $user->can('requests.decide');
    }

    private function inScope(User $user, AssignmentRequest $request): bool
    {
        return AssignmentRequest::visibleTo($user)->whereKey($request->id)->exists();
    }
}
