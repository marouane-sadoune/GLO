<?php

namespace App\Policies;

use App\Models\Establishment;
use App\Models\User;

class EstablishmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('establishments.view');
    }

    public function view(User $user, Establishment $establishment): bool
    {
        return $user->can('establishments.view') && $this->inScope($user, $establishment);
    }

    public function create(User $user): bool
    {
        return $user->can('establishments.create');
    }

    public function update(User $user, Establishment $establishment): bool
    {
        return $user->can('establishments.update') && $this->inScope($user, $establishment);
    }

    public function delete(User $user, Establishment $establishment): bool
    {
        return $user->can('establishments.delete') && $this->inScope($user, $establishment);
    }

    private function inScope(User $user, Establishment $establishment): bool
    {
        return Establishment::visibleTo($user)->whereKey($establishment->id)->exists();
    }
}
