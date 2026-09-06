<?php

namespace App\Http\Resources;

use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vacation
 */
class VacationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'logement_id' => $this->logement_id,
            'occupation_id' => $this->occupation_id,
            'occupant_id' => $this->occupant_id,
            'vacation_date' => $this->vacation_date?->toDateString(),
            'reason' => $this->reason,
            'legal_basis' => $this->legal_basis,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
