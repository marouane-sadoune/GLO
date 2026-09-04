<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Establishment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'code',
        'name_fr',
        'name_ar',
        'type',
        'address',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function logements(): HasMany
    {
        return $this->hasMany(Logement::class);
    }

    public function occupants(): HasMany
    {
        return $this->hasMany(Occupant::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
