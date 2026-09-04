<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

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
}
