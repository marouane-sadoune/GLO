<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Support\Auditing\Auditable;
use App\Support\Scoping\Scopeable;
use App\Support\Scoping\ScopesVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssignmentRequest extends Model implements ScopesVisibility
{
    use Auditable, HasFactory, Scopeable;

    protected $fillable = [
        'logement_id',
        'occupant_id',
        'status',
        'submitted_at',
        'decision_date',
        'decided_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'submitted_at' => 'date',
            'decision_date' => 'date',
        ];
    }

    public function logement(): BelongsTo
    {
        return $this->belongsTo(Logement::class);
    }

    public function occupant(): BelongsTo
    {
        return $this->belongsTo(Occupant::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function occupation(): HasOne
    {
        return $this->hasOne(Occupation::class);
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
