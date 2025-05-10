<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Facility;
use App\Models\ParkingSpot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingSpot>
 */
final class ParkingSpotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingSpot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'spot_number' => $this->faker->unique()->numberBetween(1, 100),
        ];
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