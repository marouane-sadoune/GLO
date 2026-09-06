<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OccupantStatus;
use App\Enums\UserRole;
use App\Models\Establishment;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOccupantRequest extends FormRequest
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
                Rule::requiredIf(fn () => $this->user()->roleEnum() !== UserRole::SUPER_ADMIN),
                'nullable', 'integer', 'exists:establishments,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $user = $this->user();

                    if ($value === null || $user->roleEnum() === UserRole::SUPER_ADMIN) {
                        return;
                    }

                    if (! Establishment::visibleTo($user)->whereKey($value)->exists()) {
                        $fail("Cet établissement n'est pas dans votre périmètre.");
                    }
                },
            ],
            'first_name_fr' => ['required', 'string', 'max:100'],
            'last_name_fr' => ['required', 'string', 'max:100'],
            'first_name_ar' => ['nullable', 'string', 'max:100'],
            'last_name_ar' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'employee_number' => ['nullable', 'string', 'max:30', Rule::unique('occupants', 'employee_number')->whereNull('deleted_at')],
            'framework' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'status' => ['sometimes', Rule::enum(OccupantStatus::class)],
        ];
    }
}
