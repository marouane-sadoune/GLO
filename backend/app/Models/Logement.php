<?php

namespace App\Models;

use App\Enums\HousingCategory;
use App\Enums\HousingStatus;
use App\Enums\OccupationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'inventory_number',
        'location_fr',
        'location_ar',
        'housing_category',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'housing_category' => HousingCategory::class,
            'housing_status' => HousingStatus::class,
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

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(HousingHistory::class);
    }
}
