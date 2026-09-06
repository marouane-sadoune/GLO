<?php

namespace Database\Factories;

use App\Models\Establishment;
use App\Models\Occupant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Occupant>
 */
class OccupantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'establishment_id' => Establishment::factory(),
            'first_name_fr' => fake()->firstName(),
            'last_name_fr' => fake()->lastName(),
            'employee_number' => fake()->unique()->numerify('PPR######'),
        ];
    }
}
