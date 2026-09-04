<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Establishment>
 */
class EstablishmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'code' => fake()->unique()->bothify('ETB-####'),
            'name_fr' => fake()->company(),
            'name_ar' => null,
            'type' => fake()->optional()->randomElement(['direction', 'annexe', 'centre']),
            'address' => fake()->optional()->address(),
        ];
    }
}
