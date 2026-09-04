<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'active' => true,
            'department_id' => null,
            'establishment_id' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(UserRole::SUPER_ADMIN->value);
        });
    }

    public function departmentAdmin(?Department $department = null): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $department?->id ?? Department::factory(),
            'establishment_id' => null,
        ])->afterCreating(function (User $user): void {
            $user->assignRole(UserRole::DEPARTMENT_ADMIN->value);
        });
    }

    public function establishmentManager(?Establishment $establishment = null): static
    {
        return $this->afterMaking(function (User $user) use ($establishment): void {
            $establishment ??= Establishment::factory()->create();
            $user->establishment_id = $establishment->id;
            $user->department_id = $establishment->department_id;
        })->afterCreating(function (User $user): void {
            $user->assignRole(UserRole::ESTABLISHMENT_MANAGER->value);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
