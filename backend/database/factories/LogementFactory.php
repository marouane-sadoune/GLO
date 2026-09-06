<?php

namespace Database\Factories;

use App\Enums\HousingCategory;
use App\Models\Establishment;
use App\Models\Logement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Logement>
 */
class LogementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'establishment_id' => Establishment::factory(),
            'inventory_number' => fake()->unique()->bothify('INV-####'),
            'location_fr' => fake()->streetAddress(),
            'location_ar' => null,
            'housing_category' => fake()->randomElement(HousingCategory::cases())->value,
            'notes' => null,
        ];
    }
}
