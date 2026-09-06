<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Establishment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstablishmentRequest extends FormRequest
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
        $departmentId = Establishment::find($this->route('establishment'))?->department_id;

        return [
            'code' => [
                'sometimes', 'required', 'string', 'max:30',
                Rule::unique('establishments', 'code')
                    ->where(fn ($q) => $q->where('department_id', $departmentId))
                    ->ignore($this->route('establishment'))
                    ->whereNull('deleted_at'),
            ],
            'name_fr' => ['sometimes', 'required', 'string', 'max:200'],
            'name_ar' => ['nullable', 'string', 'max:200'],
            'type' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
