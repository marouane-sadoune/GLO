<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'department_id' => [
                'nullable', 'integer', 'exists:departments,id',
                Rule::requiredIf($this->input('role') !== UserRole::SUPER_ADMIN->value),
            ],
            'establishment_id' => [
                'nullable', 'integer', 'exists:establishments,id',
                Rule::requiredIf($this->input('role') === UserRole::ESTABLISHMENT_MANAGER->value),
            ],
            'active' => ['boolean'],
        ];
    }
}
