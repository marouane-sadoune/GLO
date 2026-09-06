<?php

namespace App\Http\Filters;

use App\Support\Http\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class DocumentFilter extends QueryFilter
{
    /**
     * @return list<string>
     */
    protected function filters(): array
    {
        return ['logement_id', 'occupant_id', 'occupation_id', 'type'];
    }

    protected function filterLogementId(Builder $query, string $value): void
    {
        $query->where('logement_id', $value);
    }

    protected function filterOccupantId(Builder $query, string $value): void
    {
        $query->where('occupant_id', $value);
    }

    protected function filterOccupationId(Builder $query, string $value): void
    {
        $query->where('occupation_id', $value);
    }

    protected function filterType(Builder $query, string $value): void
    {
        $query->where('type', $value);
    }
}
