<?php

namespace App\Http\Resources;

use App\Models\Occupant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Occupant
 */
class OccupantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'establishment_id' => $this->establishment_id,
            'first_name_fr' => $this->first_name_fr,
            'last_name_fr' => $this->last_name_fr,
            'full_name_fr' => $this->full_name_fr,
            'first_name_ar' => $this->first_name_ar,
            'last_name_ar' => $this->last_name_ar,
            'full_name_ar' => $this->full_name_ar,
            'birth_date' => $this->birth_date?->toDateString(),
            'employee_number' => $this->employee_number,
            'framework' => $this->framework,
            'position' => $this->position,
            'status' => $this->status?->value,
            'establishment' => $this->whenLoaded('establishment', fn () => $this->establishment === null ? null : [
                'id' => $this->establishment->id,
                'code' => $this->establishment->code,
                'name_fr' => $this->establishment->name_fr,
                'department_id' => $this->establishment->department_id,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
