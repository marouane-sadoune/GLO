<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Support\Auditing\Auditable;
use App\Support\Scoping\Scopeable;
use App\Support\Scoping\ScopesVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model implements ScopesVisibility
{
    use Auditable, HasFactory, Scopeable;

    protected $fillable = [
        'logement_id',
        'occupant_id',
        'occupation_id',
        'type',
        'document_number',
        'document_date',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'uploaded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'document_date' => 'date',
            'size_bytes' => 'integer',
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

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected function scopeToDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereHas('logement.establishment', fn (Builder $q2) => $q2->where('department_id', $departmentId))
            ->orWhereHas('occupant.establishment', fn (Builder $q2) => $q2->where('department_id', $departmentId)));
    }

    protected function scopeToEstablishment(Builder $query, ?int $establishmentId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereHas('logement', fn (Builder $q2) => $q2->where('establishment_id', $establishmentId))
            ->orWhereHas('occupant', fn (Builder $q2) => $q2->where('establishment_id', $establishmentId)));
    }
}
