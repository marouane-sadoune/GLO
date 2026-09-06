<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Models\Logement;
use App\Models\Occupant;
use App\Models\Occupation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'type' => ['required', Rule::enum(DocumentType::class)],
            'logement_id' => ['nullable', 'integer', 'exists:logements,id'],
            'occupant_id' => ['nullable', 'integer', 'exists:occupants,id'],
            'occupation_id' => ['nullable', 'integer', 'exists:occupations,id'],
            'document_number' => ['nullable', 'string', 'max:60'],
            'document_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $logementId = $this->input('logement_id');
            $occupantId = $this->input('occupant_id');
            $occupationId = $this->input('occupation_id');

            if ($logementId === null && $occupantId === null && $occupationId === null) {
                $validator->errors()->add(
                    'logement_id',
                    'Un document doit être rattaché à un logement, un occupant ou une occupation.'
                );

                return;
            }

            $user = $this->user();

            if ($user->roleEnum() === UserRole::SUPER_ADMIN) {
                return;
            }

            if ($logementId !== null && ! Logement::visibleTo($user)->whereKey($logementId)->exists()) {
                $validator->errors()->add('logement_id', "Ce logement n'est pas dans votre périmètre.");
            }

            if ($occupantId !== null && ! Occupant::visibleTo($user)->whereKey($occupantId)->exists()) {
                $validator->errors()->add('occupant_id', "Cet occupant n'est pas dans votre périmètre.");
            }

            if ($occupationId !== null && ! Occupation::visibleTo($user)->whereKey($occupationId)->exists()) {
                $validator->errors()->add('occupation_id', "Cette occupation n'est pas dans votre périmètre.");
            }
        });
    }
}
