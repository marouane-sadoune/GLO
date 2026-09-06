<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OccupationStatus;
use App\Enums\UserRole;
use App\Models\Occupation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class RecordVacationRequest extends FormRequest
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
            'occupation_id' => [
                'required', 'integer', 'exists:occupations,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $occupation = Occupation::find($value);

                    if ($occupation === null) {
                        return;
                    }

                    if ($occupation->status !== OccupationStatus::ACTIVE) {
                        $fail("Cette occupation n'est plus active.");

                        return;
                    }

                    $user = $this->user();

                    if ($user->roleEnum() === UserRole::SUPER_ADMIN) {
                        return;
                    }

                    if (! Occupation::visibleTo($user)->whereKey($value)->exists()) {
                        $fail("Cette occupation n'est pas dans votre périmètre.");
                    }
                },
            ],
            'vacation_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'legal_basis' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
