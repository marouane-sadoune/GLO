<?php

namespace App\Http\Filters;

use App\Support\Http\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class OccupantFilter extends QueryFilter
{
    /**
     * @return list<string>
     */
    protected function filters(): array
    {
        return ['q', 'establishment_id', 'department_id', 'status'];
    }

    protected function filterQ(Builder $query, string $value): void
    {
        $query->where(fn (Builder $q) => $q
            ->where('first_name_fr', 'like', "%{$value}%")
            ->orWhere('last_name_fr', 'like', "%{$value}%")
            ->orWhere('employee_number', 'like', "%{$value}%"));
    }

    protected function filterEstablishmentId(Builder $query, string $value): void
    {
        $query->where('establishment_id', $value);
    }

    protected function filterDepartmentId(Builder $query, string $value): void
    {
        $query->whereHas('establishment', fn (Builder $q) => $q->where('department_id', $value));
    }

    protected function filterStatus(Builder $query, string $value): void
    {
        $query->where('status', $value);
    }
}
