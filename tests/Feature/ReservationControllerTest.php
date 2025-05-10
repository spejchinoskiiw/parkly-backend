<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReservationType;
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

    // Checkout functionality tests

    public function test_user_can_checkout_active_scheduled_reservation(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a user
        $user = User::factory()->create();
        
        // Create a currently active scheduled reservation
        $startTime = Carbon::now()->subHour();
        $endTime = Carbon::now()->addHour();
        
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Test the checkout endpoint
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $parkingSpot->id,
            ]);
        
        // Assert the successful response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation checked out successfully',
            ])
            ->assertJsonPath('data.id', $reservation->id);
        
        // Assert the reservation was updated in the database
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
        ]);
        
        // Refresh the reservation and check that end_time is set
        $updatedReservation = Reservation::find($reservation->id);
        $this->assertNotNull($updatedReservation->end_time);
        $this->assertTrue(Carbon::now()->diffInSeconds($updatedReservation->end_time) < 10);
    }
    
    public function test_user_can_checkout_active_ondemand_reservation(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a user
        $user = User::factory()->create();
        
        // Create a currently active on-demand reservation
        $startTime = Carbon::now()->subHour();
        
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => null,
            'type' => ReservationType::ONDEMAND,
        ]);
        
        // Test the checkout endpoint
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $parkingSpot->id,
            ]);
        
        // Assert the successful response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation checked out successfully',
            ])
            ->assertJsonPath('data.id', $reservation->id);
        
        // Refresh the reservation and check that end_time is set
        $updatedReservation = Reservation::find($reservation->id);
        $this->assertNotNull($updatedReservation->end_time);
    }
    
    public function test_checkout_returns_404_when_no_active_reservation_found(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a user
        $user = User::factory()->create();
        
        // Create a reservation that ended in the past
        $startTime = Carbon::now()->subHours(2);
        $endTime = Carbon::now()->subHour();
        
        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Test the checkout endpoint
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $parkingSpot->id,
            ]);
        
        // Assert the error response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No active reservation found for this parking spot',
            ]);
    }
    
    public function test_checkout_validates_parking_spot_id(): void
    {
        $user = User::factory()->create();
        
        // Test with missing parking_spot_id
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parking_spot_id']);
        
        // Test with non-numeric parking_spot_id
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => 'not-a-number',
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parking_spot_id']);
        
        // Test with non-existent parking spot
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => 9999, // Assuming this ID doesn't exist
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parking_spot_id']);
    }
    
    public function test_checkout_requires_authentication(): void
    {
        $response = $this->postJson('/api/reservations/checkout', [
            'parking_spot_id' => 1,
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_user_cannot_checkout_another_users_reservation(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Create a reservation for user1
        $startTime = Carbon::now()->subHour();
        $endTime = Carbon::now()->addHour();
        
        Reservation::factory()->create([
            'user_id' => $user1->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Try to checkout as user2
        $response = $this->actingAs($user2)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $parkingSpot->id,
            ]);
        
        // Should get a 404 because no active reservation exists for user2
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No active reservation found for this parking spot',
            ]);
    }
    
    public function test_checkout_future_reservation_returns_404(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a user
        $user = User::factory()->create();
        
        // Create a reservation that starts in the future
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHours(2);
        
        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Test the checkout endpoint
        $response = $this->actingAs($user)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $parkingSpot->id,
            ]);
        
        // Assert the error response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No active reservation found for this parking spot',
            ]);
    }

    public function test_user_can_get_active_reservations(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a parking spot
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a user
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $now = Carbon::now();
        
        // Create an active scheduled reservation (current time is between start and end)
        $activeScheduled = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Create an active on-demand reservation (started but not ended)
        $activeOnDemand = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $now->copy()->subHour(),
            'end_time' => null,
            'type' => ReservationType::ONDEMAND,
        ]);
        
        // Create a pending reservation (start time in the future)
        $pendingReservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $now->copy()->addHours(2),
            'end_time' => $now->copy()->addHours(4),
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Create a past reservation (ended)
        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $now->copy()->subHours(3),
            'end_time' => $now->copy()->subHour(),
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Create a reservation for another user (should not be returned)
        Reservation::factory()->create([
            'user_id' => $otherUser->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Test the active reservations endpoint
        $response = $this->actingAs($user)
            ->getJson('/api/reservations/active');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'parking_spot_id',
                        'start_time',
                        'end_time',
                        'type',
                        'parkingSpot',
                    ],
                ],
            ]);
        
        // Should return 3 reservations: 1 active scheduled, 1 active on-demand, and 1 pending
        $this->assertCount(3, $response->json('data'));
        
        // Extract the IDs from the response
        $responseIds = collect($response->json('data'))->pluck('id')->toArray();
        
        // Check that the active scheduled, active on-demand, and pending reservations are in the response
        $this->assertContains($activeScheduled->id, $responseIds);
        $this->assertContains($activeOnDemand->id, $responseIds);
        $this->assertContains($pendingReservation->id, $responseIds);
    }
    
    public function test_active_reservations_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/reservations/active');
        
        $response->assertStatus(401);
    }
} 