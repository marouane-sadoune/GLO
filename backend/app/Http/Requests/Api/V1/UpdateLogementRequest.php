<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\HousingCategory;
use App\Models\Logement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLogementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $establishmentId = Logement::find($this->route('logement'))?->establishment_id;

        return [
            'inventory_number' => [
                'sometimes', 'required', 'string', 'max:60',
                Rule::unique('logements', 'inventory_number')
                    ->where(fn ($q) => $q->where('establishment_id', $establishmentId))
                    ->ignore($this->route('logement'))
                    ->whereNull('deleted_at'),
            ],
            'location_fr' => ['sometimes', 'required', 'string', 'max:255'],
            'location_ar' => ['nullable', 'string', 'max:255'],
            'housing_category' => ['sometimes', 'required', Rule::enum(HousingCategory::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
