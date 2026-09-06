<?php

namespace App\Policies;

use App\Models\Logement;
use App\Models\User;

class LogementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('logements.view');
    }

    public function view(User $user, Logement $logement): bool
    {
        return $user->can('logements.view') && $this->inScope($user, $logement);
    }

    public function create(User $user): bool
    {
        return $user->can('logements.create');
    }

    public function update(User $user, Logement $logement): bool
    {
        return $user->can('logements.update') && $this->inScope($user, $logement);
    }

    public function delete(User $user, Logement $logement): bool
    {
        return $user->can('logements.delete') && $this->inScope($user, $logement);
    }

    public function viewHistory(User $user, Logement $logement): bool
    {
        return $user->can('history.view') && $this->inScope($user, $logement);
    }

    private function inScope(User $user, Logement $logement): bool
    {
        return Logement::visibleTo($user)->whereKey($logement->id)->exists();
    }
}
