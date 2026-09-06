<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AssignmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptAssignmentRequestRequest extends FormRequest
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
            'assignment_type' => ['required', Rule::enum(AssignmentType::class)],
            'assignment_date' => ['required', 'date'],
            'start_date' => ['required', 'date', 'after_or_equal:assignment_date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
