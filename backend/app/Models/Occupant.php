<?php

namespace App\Models;

use App\Enums\OccupantStatus;
use App\Enums\OccupationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occupant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'first_name_fr',
        'last_name_fr',
        'first_name_ar',
        'last_name_ar',
        'birth_date',
        'employee_number',
        'framework',
        'position',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'status' => OccupantStatus::class,
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function occupations(): HasMany
    {
        return $this->hasMany(Occupation::class);
    }

    public function activeOccupation(): HasOne
    {
        return $this->hasOne(Occupation::class)->where('status', OccupationStatus::ACTIVE->value);
    }

    public function assignmentRequests(): HasMany
    {
        return $this->hasMany(AssignmentRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getFullNameFrAttribute(): string
    {
        return "{$this->first_name_fr} {$this->last_name_fr}";
    }

    public function getFullNameArAttribute(): ?string
    {
        if ($this->first_name_ar && $this->last_name_ar) {
            return "{$this->first_name_ar} {$this->last_name_ar}";
        }
        return null;
    }
}
