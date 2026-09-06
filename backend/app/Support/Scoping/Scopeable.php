<?php

namespace App\Support\Scoping;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Default implementation of ScopesVisibility for models reachable through
 * an `establishment_id` column and an `establishment()` relation
 * (e.g. Logement, Occupant). Models reached through another relation
 * (e.g. Occupation via `logement`) or scoped by their own columns
 * (e.g. Establishment, User) override scopeToDepartment()/scopeToEstablishment().
 *
 * SUPER_ADMIN always sees every row; every other role is scoped or excluded.
 */
trait Scopeable
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->roleEnum()) {
            UserRole::SUPER_ADMIN => $query,
            UserRole::DEPARTMENT_ADMIN => $this->scopeToDepartment($query, $user->department_id),
            UserRole::ESTABLISHMENT_MANAGER => $this->scopeToEstablishment($query, $user->establishment_id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    protected function scopeToDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $query->whereHas(
            'establishment',
            fn (Builder $q) => $q->where('department_id', $departmentId)
        );
    }

    protected function scopeToEstablishment(Builder $query, ?int $establishmentId): Builder
    {
        return $query->where('establishment_id', $establishmentId);
    }
}
