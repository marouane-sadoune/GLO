<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\HousingHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        AuditAction $action,
        Model $subject,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $logementId = null,
        ?string $description = null,
    ): HousingHistory {
        return HousingHistory::create([
            'logement_id' => $logementId,
            'auditable_type' => $subject->getMorphClass(),
            'auditable_id' => $subject->getKey(),
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description ?? sprintf(
                '%s %s #%s',
                class_basename($subject),
                strtolower($action->value),
                $subject->getKey()
            ),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}
