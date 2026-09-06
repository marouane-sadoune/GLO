<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\HousingCategory;
use App\Enums\UserRole;
use App\Models\Establishment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLogementRequest extends FormRequest
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
        return [
            'establishment_id' => [
                'required', 'integer', 'exists:establishments,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = $this->user();

                    if ($user->roleEnum() === UserRole::SUPER_ADMIN) {
                        return;
                    }

                    if (! Establishment::visibleTo($user)->whereKey($value)->exists()) {
                        $fail("Cet établissement n'est pas dans votre périmètre.");
                    }
                },
            ],
            'inventory_number' => [
                'required', 'string', 'max:60',
                Rule::unique('logements', 'inventory_number')
                    ->where(fn ($q) => $q->where('establishment_id', $this->input('establishment_id')))
                    ->whereNull('deleted_at'),
            ],
            'location_fr' => ['required', 'string', 'max:255'],
            'location_ar' => ['nullable', 'string', 'max:255'],
            'housing_category' => ['required', Rule::enum(HousingCategory::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
