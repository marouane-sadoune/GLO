<?php

namespace App\Http\Resources;

use App\Models\Logement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Logement
 */
class LogementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'establishment_id' => $this->establishment_id,
            'inventory_number' => $this->inventory_number,
            'location_fr' => $this->location_fr,
            'location_ar' => $this->location_ar,
            'housing_category' => $this->housing_category?->value,
            'housing_status' => $this->housing_status?->value,
            'notes' => $this->notes,
            'establishment' => $this->whenLoaded('establishment', fn () => [
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
