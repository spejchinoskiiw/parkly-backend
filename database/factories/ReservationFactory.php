<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReservationType;
use App\Models\ParkingSpot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHours(3);
        
        return [
            'user_id' => User::factory(),
            'parking_spot_id' => ParkingSpot::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => $this->faker->randomElement(ReservationType::cases()),
        ];
    }
    
    /**
     * Configure the model factory to create an ondemand reservation.
     */
    public function ondemand(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ReservationType::ONDEMAND,
                'end_time' => null,
            ];
        });
    }
    
    /**
     * Configure the model factory to create a scheduled reservation.
     */
    public function scheduled(): Factory
    {
        return $this->state(function (array $attributes) {
            $startTime = $attributes['start_time'] ?? Carbon::now()->addHour();
            $endTime = Carbon::parse($startTime)->addHours(2);
            
            return [
                'type' => ReservationType::SCHEDULED,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }
} 