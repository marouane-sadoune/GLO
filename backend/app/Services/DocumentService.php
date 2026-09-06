<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads on the private "local" disk (storage/app/private) under a
 * UUID filename — never the user's own filename — and only ever serves them
 * back through a policy-checked download route (§11: never Storage::url(),
 * never the public disk; these are official personnel documents).
 */
class DocumentService
{
    /**
     * @param  array{logement_id?: int|null, occupant_id?: int|null, occupation_id?: int|null, type: string, document_number?: string|null, document_date?: string|null, notes?: string|null}  $attributes
     */
    public function store(UploadedFile $file, array $attributes, User $uploader): Document
    {
        $path = $file->storeAs(
            'documents/'.now()->format('Y'),
            Str::uuid()->toString().'.'.$file->getClientOriginalExtension(),
            'local',
        );

        $document = Model::withoutEvents(fn () => Document::create([
            ...$attributes,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $uploader->id,
        ]));

        app(AuditLogger::class)->log(
            action: AuditAction::DOCUMENT_UPLOADED,
            subject: $document,
            newValues: $document->getAttributes(),
            logementId: $document->logement_id,
        );

        return $document;
    }

    public function delete(Document $document): void
    {
        app(AuditLogger::class)->log(
            action: AuditAction::DOCUMENT_DELETED,
            subject: $document,
            oldValues: $document->getAttributes(),
            logementId: $document->logement_id,
        );

        Storage::disk('local')->delete($document->file_path);

        Model::withoutEvents(fn () => $document->delete());
    }
}
