<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use App\Models\Logement;
use App\Models\Occupant;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequestRequest extends FormRequest
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
            'logement_id' => [
                'required', 'integer', 'exists:logements,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $user = $this->user();

                    if ($user->roleEnum() === UserRole::SUPER_ADMIN) {
                        return;
                    }

                    if (! Logement::visibleTo($user)->whereKey($value)->exists()) {
                        $fail("Ce logement n'est pas dans votre périmètre.");
                    }
                },
            ],
            'occupant_id' => [
                'required', 'integer', 'exists:occupants,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $user = $this->user();

                    if ($user->roleEnum() === UserRole::SUPER_ADMIN) {
                        return;
                    }

                    if (! Occupant::visibleTo($user)->whereKey($value)->exists()) {
                        $fail("Cet occupant n'est pas dans votre périmètre.");
                    }
                },
            ],
            'notes' => ['nullable', 'string'],
        ];
    }
}
