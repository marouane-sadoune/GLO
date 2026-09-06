<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AssignmentRequest
 */
class AssignmentRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'logement_id' => $this->logement_id,
            'occupant_id' => $this->occupant_id,
            'status' => $this->status?->value,
            'submitted_at' => $this->submitted_at?->toDateString(),
            'decision_date' => $this->decision_date?->toDateString(),
            'decided_by' => $this->decided_by,
            'notes' => $this->notes,
            'logement' => $this->whenLoaded('logement', fn () => [
                'id' => $this->logement->id,
                'inventory_number' => $this->logement->inventory_number,
                'location_fr' => $this->logement->location_fr,
                'establishment_id' => $this->logement->establishment_id,
            ]),
            'occupant' => $this->whenLoaded('occupant', fn () => [
                'id' => $this->occupant->id,
                'full_name_fr' => $this->occupant->full_name_fr,
                'employee_number' => $this->occupant->employee_number,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
