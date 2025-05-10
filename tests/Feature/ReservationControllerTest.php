<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_get_reservations_for_date(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a user
        $user = User::factory()->create();
        
        $targetDate = Carbon::today();
        $formattedDate = $targetDate->format('Y-m-d');
        
        // Create reservations for the user on the target date
        $reservation1 = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $targetDate->copy()->setHour(9),
            'end_time' => $targetDate->copy()->setHour(12),
            'type' => 'scheduled',
        ]);
        
        $reservation2 = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $targetDate->copy()->setHour(14),
            'end_time' => $targetDate->copy()->setHour(17),
            'type' => 'scheduled',
        ]);
        
        // Create a reservation for a different date
        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $targetDate->copy()->addDay()->setHour(10),
            'end_time' => $targetDate->copy()->addDay()->setHour(15),
            'type' => 'scheduled',
        ]);
        
        // Test the API endpoint
        $response = $this->actingAs($user)
            ->getJson("/api/reservations/reservationsForDate?date={$formattedDate}");
        
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $reservation1->id)
            ->assertJsonPath('data.1.id', $reservation2->id);
    }
    
    public function test_user_cannot_get_reservations_with_invalid_date_format(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/reservations/reservationsForDate?date=invalid-date');
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }
    
    public function test_user_cannot_get_reservations_without_date(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/reservations/reservationsForDate');
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }
    
    public function test_unauthenticated_user_cannot_access_endpoint(): void
    {
        $response = $this->getJson('/api/reservations/reservationsForDate?date=2023-05-20');
        
        $response->assertStatus(401);
    }
} 