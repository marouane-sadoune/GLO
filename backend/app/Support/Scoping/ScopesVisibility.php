<?php

namespace App\Support\Scoping;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for models whose rows are visible only within a user's
 * department or establishment scope (see docs/ARCHITECTURE.md §8).
 */
interface ScopesVisibility
{
    public function scopeVisibleTo(Builder $query, User $user): Builder;
}
