<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => UserRole::USER->value,
            'facility_id' => Facility::factory(),
            'remember_token' => Str::random(10),
        ];
    }
    
    /**
     * Configure the user as an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN->value,
        ]);
    }
    
    /**
     * Configure the user as a manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::MANAGER->value,
        ]);
    }
    
    /**
     * Configure the model factory to belong to a specific facility.
     */
    public function forFacility(Facility $facility): static
    {
        return $this->state(fn (array $attributes) => [
            'facility_id' => $facility->id,
        ]);
    }
}
