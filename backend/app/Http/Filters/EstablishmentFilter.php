<?php

namespace App\Http\Filters;

use App\Support\Http\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class EstablishmentFilter extends QueryFilter
{
    /**
     * @return list<string>
     */
    protected function filters(): array
    {
        return ['q', 'department_id'];
    }

    protected function filterQ(Builder $query, string $value): void
    {
        $query->where(fn (Builder $q) => $q
            ->where('name_fr', 'like', "%{$value}%")
            ->orWhere('code', 'like', "%{$value}%"));
    }

    protected function filterDepartmentId(Builder $query, string $value): void
    {
        $query->where('department_id', $value);
    }
}
