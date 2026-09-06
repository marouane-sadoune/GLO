<?php

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Models\AssignmentRequest;
use App\Models\Logement;
use App\Models\Occupant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentRequest>
 */
class AssignmentRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'logement_id' => Logement::factory(),
            'occupant_id' => Occupant::factory(),
            'status' => RequestStatus::PENDING->value,
            'submitted_at' => now()->toDateString(),
        ];
    }
}
