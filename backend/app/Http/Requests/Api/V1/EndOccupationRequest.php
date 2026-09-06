<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OccupationEndReason;
use App\Models\Occupation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EndOccupationRequest extends FormRequest
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
        $occupation = Occupation::find($this->route('occupation'));

        return [
            'end_reason' => ['required', Rule::enum(OccupationEndReason::class)],
            'end_date' => array_filter([
                'required',
                'date',
                $occupation ? 'after_or_equal:'.$occupation->start_date->toDateString() : null,
            ]),
            'notes' => ['nullable', 'string'],
        ];
    }
}
