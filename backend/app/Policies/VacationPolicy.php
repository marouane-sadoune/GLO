<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacation;

class VacationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vacations.view');
    }

    public function view(User $user, Vacation $vacation): bool
    {
        return $user->can('vacations.view') && $this->inScope($user, $vacation);
    }

    public function create(User $user): bool
    {
        return $user->can('vacations.create');
    }

    private function inScope(User $user, Vacation $vacation): bool
    {
        return Vacation::visibleTo($user)->whereKey($vacation->id)->exists();
    }
}
