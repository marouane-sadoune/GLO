<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
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
            'code' => [
                'sometimes', 'required', 'string', 'max:10',
                Rule::unique('departments', 'code')->ignore($this->route('department')),
            ],
            'name_fr' => ['sometimes', 'required', 'string', 'max:150'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:150'],
        ];
    }
}
