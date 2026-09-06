<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OccupantStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOccupantRequest extends FormRequest
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
            // Reassigning the scoping anchor is a SUPER_ADMIN-only move (AMB-03).
            'establishment_id' => [
                $this->user()->roleEnum() === UserRole::SUPER_ADMIN ? 'sometimes' : 'prohibited',
                'nullable', 'integer', 'exists:establishments,id',
            ],
            'first_name_fr' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name_fr' => ['sometimes', 'required', 'string', 'max:100'],
            'first_name_ar' => ['nullable', 'string', 'max:100'],
            'last_name_ar' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'employee_number' => [
                'nullable', 'string', 'max:30',
                Rule::unique('occupants', 'employee_number')
                    ->ignore($this->route('occupant'))
                    ->whereNull('deleted_at'),
            ],
            'framework' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'status' => ['sometimes', Rule::enum(OccupantStatus::class)],
        ];
    }
}
