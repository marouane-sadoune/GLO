<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Support\Scoping\ScopesVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Department extends Model implements ScopesVisibility
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

    /**
     * A department admin's and an establishment manager's own department is visible;
     * establishment managers have `department_id` kept in sync with their establishment (AMB-11).
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->roleEnum()) {
            UserRole::SUPER_ADMIN => $query,
            default => $query->where('id', $user->department_id),
        };
    }
}
