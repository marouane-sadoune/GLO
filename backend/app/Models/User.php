<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Support\Scoping\Scopeable;
use App\Support\Scoping\ScopesVisibility;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements ScopesVisibility
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, Scopeable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'establishment_id',
        'active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(HousingHistory::class);
    }

    public function getRoleAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function roleEnum(): ?UserRole
    {
        return $this->role ? UserRole::from($this->role) : null;
    }

    protected function scopeToDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }
}
