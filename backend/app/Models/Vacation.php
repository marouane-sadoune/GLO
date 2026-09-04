<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation extends Model
{
    use HasFactory;

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
}
