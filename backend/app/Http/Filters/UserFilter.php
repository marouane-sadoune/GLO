<?php

namespace App\Http\Filters;

use App\Support\Http\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends QueryFilter
{
    /**
     * @return list<string>
     */
    protected function filters(): array
    {
        return ['q', 'role', 'department_id', 'establishment_id'];
    }

    protected function filterQ(Builder $query, string $value): void
    {
        $query->where(fn (Builder $q) => $q
            ->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%"));
    }

    protected function filterRole(Builder $query, string $value): void
    {
        $query->whereHas('roles', fn (Builder $q) => $q->where('name', $value));
    }

    protected function filterDepartmentId(Builder $query, string $value): void
    {
        $query->where('department_id', $value);
    }

    protected function filterEstablishmentId(Builder $query, string $value): void
    {
        $query->where('establishment_id', $value);
    }
}
