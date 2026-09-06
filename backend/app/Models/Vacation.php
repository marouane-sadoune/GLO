<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use App\Support\Scoping\Scopeable;
use App\Support\Scoping\ScopesVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation extends Model implements ScopesVisibility
{
    use Auditable, HasFactory, Scopeable;

    protected $fillable = [
        'logement_id',
        'occupation_id',
        'occupant_id',
        'vacation_date',
        'reason',
        'legal_basis',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vacation_date' => 'date',
        ];
    }

    public function logement(): BelongsTo
    {
        return $this->belongsTo(Logement::class);
    }

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function occupant(): BelongsTo
    {
        return $this->belongsTo(Occupant::class);
    }

    protected function scopeToDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $query->whereHas(
            'logement.establishment',
            fn (Builder $q) => $q->where('department_id', $departmentId)
        );
    }

    protected function scopeToEstablishment(Builder $query, ?int $establishmentId): Builder
    {
        return $query->whereHas(
            'logement',
            fn (Builder $q) => $q->where('establishment_id', $establishmentId)
        );
    }
}
