<?php

namespace App\Support\Auditing;

use App\Enums\AuditAction;
use App\Models\Logement;
use App\Services\AuditLogger;

/**
 * Writes a housing_history row for every create/update/delete, via AuditLogger.
 * Never applied to User: attributes are logged verbatim, and a hashed
 * password has no business sitting in an audit trail.
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            $model->recordAudit(AuditAction::CREATED, null, $model->getAttributes());
        });

        static::updated(function (self $model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $model->recordAudit(
                AuditAction::UPDATED,
                array_intersect_key($model->getOriginal(), $changes),
                $changes
            );
        });

        static::deleted(function (self $model): void {
            $model->recordAudit(AuditAction::DELETED, $model->getOriginal(), null);
        });
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected function recordAudit(AuditAction $action, ?array $oldValues, ?array $newValues): void
    {
        app(AuditLogger::class)->log(
            action: $action,
            subject: $this,
            oldValues: $oldValues,
            newValues: $newValues,
            logementId: $this instanceof Logement ? $this->getKey() : $this->getAttribute('logement_id'),
        );
    }
}
