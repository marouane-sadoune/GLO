<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\OccupationEndReason;
use App\Enums\OccupationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Occupation extends Model
{
    use HasFactory;

    protected $fillable = [
        'logement_id',
        'occupant_id',
        'assignment_request_id',
        'assignment_date',
        'assignment_type',
        'start_date',
        'end_date',
        'end_reason',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assignment_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'assignment_type' => AssignmentType::class,
            'end_reason' => OccupationEndReason::class,
            'status' => OccupationStatus::class,
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

    public function assignmentRequest(): BelongsTo
    {
        return $this->belongsTo(AssignmentRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function vacation(): HasOne
    {
        return $this->hasOne(Vacation::class);
    }
}
