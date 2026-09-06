<?php

namespace App\Policies;

use App\Models\Occupation;
use App\Models\User;

class OccupationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('occupations.view');
    }

    public function view(User $user, Occupation $occupation): bool
    {
        return $user->can('occupations.view') && $this->inScope($user, $occupation);
    }

    public function manage(User $user, Occupation $occupation): bool
    {
        return $user->can('occupations.manage') && $this->inScope($user, $occupation);
    }

    private function inScope(User $user, Occupation $occupation): bool
    {
        return Occupation::visibleTo($user)->whereKey($occupation->id)->exists();
    }
}
