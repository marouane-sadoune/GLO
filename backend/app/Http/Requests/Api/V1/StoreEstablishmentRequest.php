<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEstablishmentRequest extends FormRequest
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
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'code' => [
                'required', 'string', 'max:30',
                Rule::unique('establishments', 'code')
                    ->where(fn ($q) => $q->where('department_id', $this->input('department_id')))
                    ->whereNull('deleted_at'),
            ],
            'name_fr' => ['required', 'string', 'max:200'],
            'name_ar' => ['nullable', 'string', 'max:200'],
            'type' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();

            if ($user->roleEnum() === UserRole::SUPER_ADMIN) {
                return;
            }

            if ((int) $this->input('department_id') !== $user->department_id) {
                $validator->errors()->add(
                    'department_id',
                    'Vous ne pouvez créer un établissement que dans votre propre département.'
                );
            }
        });
    }
}
