<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Facility>
 */
final class FacilityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Facility::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Parking',
            'parking_spot_count' => $this->faker->numberBetween(5, 100),
            'manager_id' => null,
        ];
    }

    /**
     * Configure the model factory to have a manager.
     */
    public function withManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => User::factory()->create(['role' => 'manager'])->id,
        ]);
    }
} 