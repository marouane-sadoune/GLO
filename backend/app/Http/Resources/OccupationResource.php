<?php

namespace App\Http\Resources;

use App\Models\Occupation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Occupation
 */
class OccupationResource extends JsonResource
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
            'assignment_request_id' => $this->assignment_request_id,
            'assignment_date' => $this->assignment_date?->toDateString(),
            'assignment_type' => $this->assignment_type?->value,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'end_reason' => $this->end_reason?->value,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'logement' => $this->whenLoaded('logement', fn () => [
                'id' => $this->logement->id,
                'inventory_number' => $this->logement->inventory_number,
                'location_fr' => $this->logement->location_fr,
            ]),
            'occupant' => $this->whenLoaded('occupant', fn () => [
                'id' => $this->occupant->id,
                'full_name_fr' => $this->occupant->full_name_fr,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
