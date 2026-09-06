<?php

namespace Database\Factories;

use App\Enums\AssignmentType;
use App\Models\Logement;
use App\Models\Occupant;
use App\Models\Occupation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Occupation>
 */
class OccupationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'logement_id' => Logement::factory(),
            'occupant_id' => Occupant::factory(),
            'assignment_date' => now()->subYear()->toDateString(),
            'assignment_type' => fake()->randomElement(AssignmentType::cases())->value,
            'start_date' => now()->subYear()->toDateString(),
        ];
    }
}
