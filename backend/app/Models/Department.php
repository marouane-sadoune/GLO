<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_fr',
        'name_ar',
    ];

    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class);
    }

    public function logements(): HasManyThrough
    {
        return $this->hasManyThrough(Logement::class, Establishment::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
