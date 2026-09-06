<?php

namespace App\Http\Filters;

use App\Support\Http\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class LogementFilter extends QueryFilter
{
    /**
     * @return list<string>
     */
    protected function filters(): array
    {
        return ['q', 'establishment_id', 'department_id', 'housing_category', 'housing_status'];
    }

    protected function filterQ(Builder $query, string $value): void
    {
        $query->where(fn (Builder $q) => $q
            ->where('inventory_number', 'like', "%{$value}%")
            ->orWhere('location_fr', 'like', "%{$value}%"));
    }

    protected function filterEstablishmentId(Builder $query, string $value): void
    {
        $query->where('establishment_id', $value);
    }

    protected function filterDepartmentId(Builder $query, string $value): void
    {
        $query->whereHas('establishment', fn (Builder $q) => $q->where('department_id', $value));
    }

    protected function filterHousingCategory(Builder $query, string $value): void
    {
        $query->where('housing_category', $value);
    }

    protected function filterHousingStatus(Builder $query, string $value): void
    {
        $query->where('housing_status', $value);
    }
}
