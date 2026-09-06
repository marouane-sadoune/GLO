<?php

namespace App\Policies;

use App\Models\Occupant;
use App\Models\User;

class OccupantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('occupants.view');
    }

    public function view(User $user, Occupant $occupant): bool
    {
        return $user->can('occupants.view') && $this->inScope($user, $occupant);
    }

    public function create(User $user): bool
    {
        return $user->can('occupants.create');
    }

    public function update(User $user, Occupant $occupant): bool
    {
        return $user->can('occupants.update') && $this->inScope($user, $occupant);
    }

    public function delete(User $user, Occupant $occupant): bool
    {
        return $user->can('occupants.delete') && $this->inScope($user, $occupant);
    }

    private function inScope(User $user, Occupant $occupant): bool
    {
        return Occupant::visibleTo($user)->whereKey($occupant->id)->exists();
    }
}
