<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\ReservationType;
use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;
    
    private ReservationService $reservationService;
    private User $user;
    private ParkingSpot $parkingSpot;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->reservationService = app(ReservationService::class);
        
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create a user
        $this->user = User::factory()->create([
            'facility_id' => $facility->id,
        ]);
        
        // Create a parking spot
        $this->parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
        ]);
    }
    
    public function testCreateOnDemandReservationSuccess(): void
    {
        $startTime = Carbon::now()->addHour();
        
        $reservation = $this->reservationService->createOnDemandReservation(
            $this->user,
            $this->parkingSpot,
            $startTime
        );
        
        $this->assertNotNull($reservation);
        $this->assertEquals($this->user->id, $reservation->user_id);
        $this->assertEquals($this->parkingSpot->id, $reservation->parking_spot_id);
        $this->assertEquals($startTime->toDateTimeString(), $reservation->start_time->toDateTimeString());
        $this->assertNull($reservation->end_time);
        $this->assertEquals(ReservationType::ONDEMAND, $reservation->type);
    }
    
    public function testCreateOnDemandReservationFailsWhenSpotNotAvailable(): void
    {
        $startTime = Carbon::now()->addHour();
        
        // Create an existing reservation for the spot
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $startTime,
            'type' => ReservationType::ONDEMAND,
        ]);
        
        $reservation = $this->reservationService->createOnDemandReservation(
            $this->user,
            $this->parkingSpot,
            $startTime
        );
        
        $this->assertNull($reservation);
    }
    
    public function testCreateScheduledReservationSuccess(): void
    {
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHours(3);
        
        $reservation = $this->reservationService->createScheduledReservation(
            $this->user,
            $this->parkingSpot,
            $startTime,
            $endTime
        );
        
        $this->assertNotNull($reservation);
        $this->assertEquals($this->user->id, $reservation->user_id);
        $this->assertEquals($this->parkingSpot->id, $reservation->parking_spot_id);
        $this->assertEquals($startTime->toDateTimeString(), $reservation->start_time->toDateTimeString());
        $this->assertEquals($endTime->toDateTimeString(), $reservation->end_time->toDateTimeString());
        $this->assertEquals(ReservationType::SCHEDULED, $reservation->type);
    }
    
    public function testCreateScheduledReservationFailsWhenOverlapping(): void
    {
        $existingStartTime = Carbon::now()->addHour();
        $existingEndTime = Carbon::now()->addHours(3);
        
        // Create an existing scheduled reservation
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $existingStartTime,
            'end_time' => $existingEndTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Try to create an overlapping reservation (starting during the existing reservation)
        $newStartTime = Carbon::now()->addHours(2);
        $newEndTime = Carbon::now()->addHours(4);
        
        $reservation = $this->reservationService->createScheduledReservation(
            $this->user,
            $this->parkingSpot,
            $newStartTime,
            $newEndTime
        );
        
        $this->assertNull($reservation);
    }
    
    public function testCreateScheduledReservationFailsWithInvalidTimeRange(): void
    {
        $startTime = Carbon::now()->addHour();
        $endTime = $startTime->copy(); // Same time, should fail
        
        $reservation = $this->reservationService->createScheduledReservation(
            $this->user,
            $this->parkingSpot,
            $startTime,
            $endTime
        );
        
        $this->assertNull($reservation);
    }

    public function test_get_user_reservations_for_date(): void
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
        
        // Create a reservation for a different user
        $otherUser = User::factory()->create();
        Reservation::factory()->create([
            'user_id' => $otherUser->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $targetDate->copy()->setHour(11),
            'end_time' => $targetDate->copy()->setHour(13),
            'type' => 'scheduled',
        ]);
        
        // Test retrieving reservations for the target date
        $reservations = $this->reservationService->getUserReservationsForDate($user, $targetDate);
        
        // Assert that only the reservations for the user on the target date are returned
        $this->assertCount(2, $reservations);
        $this->assertTrue($reservations->contains('id', $reservation1->id));
        $this->assertTrue($reservations->contains('id', $reservation2->id));
    }

    public function test_get_available_spots_with_time_slots(): void
    {
        // Create a facility
        $facility = Facility::factory()->create();
        
        // Create two parking spots
        $spot1 = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        $spot2 = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 2,
        ]);
        
        // Set fixed date for testing
        $testDate = Carbon::parse('2023-05-20');
        $workStart = Carbon::parse('2023-05-20 08:00:00');
        $workEnd = Carbon::parse('2023-05-20 17:00:00');
        
        // Create a reservation for spot 1 from 13:00 to 15:00
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $spot1->id,
            'start_time' => Carbon::parse('2023-05-20 13:00:00'),
            'end_time' => Carbon::parse('2023-05-20 15:00:00'),
            'type' => 'scheduled',
        ]);
        
        // Get available spots
        $availableSpots = $this->reservationService->getAvailableSpotsWithTimeSlots($facility->id, $testDate);
        
        // Verify structure
        $this->assertIsArray($availableSpots);
        $this->assertArrayHasKey('1', $availableSpots);
        $this->assertArrayHasKey('2', $availableSpots);
        
        // Check that both spots have the expected structure
        $this->assertArrayHasKey('time_slots', $availableSpots[1]);
        $this->assertArrayHasKey('all_day', $availableSpots[1]);
        $this->assertArrayHasKey('time_slots', $availableSpots[2]);
        $this->assertArrayHasKey('all_day', $availableSpots[2]);
        
        // Verify spot 1 has the right time slots and is not all day
        $this->assertCount(2, $availableSpots[1]['time_slots']);
        $this->assertEquals($workStart->format('Y-m-d H:i:s'), $availableSpots[1]['time_slots'][0]['start']);
        $this->assertEquals(Carbon::parse('2023-05-20 13:00:00')->format('Y-m-d H:i:s'), $availableSpots[1]['time_slots'][0]['end']);
        $this->assertEquals(Carbon::parse('2023-05-20 15:00:00')->format('Y-m-d H:i:s'), $availableSpots[1]['time_slots'][1]['start']);
        $this->assertEquals($workEnd->format('Y-m-d H:i:s'), $availableSpots[1]['time_slots'][1]['end']);
        $this->assertFalse($availableSpots[1]['all_day']);
        
        // Verify spot 2 has one time slot for the whole day and is marked as all day
        $this->assertCount(1, $availableSpots[2]['time_slots']);
        $this->assertEquals($workStart->format('Y-m-d H:i:s'), $availableSpots[2]['time_slots'][0]['start']);
        $this->assertEquals($workEnd->format('Y-m-d H:i:s'), $availableSpots[2]['time_slots'][0]['end']);
        $this->assertTrue($availableSpots[2]['all_day']);
    }

    public function test_checkout_reservation_for_active_scheduled_reservation(): void
    {
        // Create a scheduled reservation that is currently active
        $startTime = Carbon::now()->subHour();
        $endTime = Carbon::now()->addHour();
        
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Checkout the reservation
        $result = $this->reservationService->checkoutReservation($this->user, $this->parkingSpot->id);
        
        // Assert the reservation was updated
        $this->assertNotNull($result);
        $this->assertEquals($reservation->id, $result->id);
        $this->assertNotNull($result->end_time);
        $this->assertTrue(Carbon::now()->isSameDay($result->end_time));
        
        // Assert the end time was set to now (allow for 5 seconds difference for test execution)
        $this->assertTrue(Carbon::now()->diffInSeconds($result->end_time) < 5);
    }
    
    public function test_checkout_reservation_for_active_ondemand_reservation(): void
    {
        // Create an on-demand reservation that is currently active
        $startTime = Carbon::now()->subHour();
        
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => null,
            'type' => ReservationType::ONDEMAND,
        ]);
        
        // Checkout the reservation
        $result = $this->reservationService->checkoutReservation($this->user, $this->parkingSpot->id);
        
        // Assert the reservation was updated
        $this->assertNotNull($result);
        $this->assertEquals($reservation->id, $result->id);
        $this->assertNotNull($result->end_time);
        $this->assertTrue(Carbon::now()->isSameDay($result->end_time));
        
        // Assert the end time was set to now (allow for 5 seconds difference for test execution)
        $this->assertTrue(Carbon::now()->diffInSeconds($result->end_time) < 5);
    }
    
    public function test_checkout_reservation_returns_null_when_no_active_reservation(): void
    {
        // Create a reservation that ended in the past
        $startTime = Carbon::now()->subHours(2);
        $endTime = Carbon::now()->subHour();
        
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Try to checkout a non-active reservation
        $result = $this->reservationService->checkoutReservation($this->user, $this->parkingSpot->id);
        
        // Assert no reservation was found/updated
        $this->assertNull($result);
    }
    
    public function test_checkout_reservation_returns_null_for_future_reservation(): void
    {
        // Create a reservation that starts in the future
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHours(2);
        
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Try to checkout a future reservation
        $result = $this->reservationService->checkoutReservation($this->user, $this->parkingSpot->id);
        
        // Assert no reservation was found/updated
        $this->assertNull($result);
    }

    public function test_get_user_active_reservations(): void
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
        
        // Get active and pending reservations
        $activeReservations = $this->reservationService->getUserActiveReservations($user);
        
        // Should return 3 reservations: 1 active scheduled, 1 active on-demand, and 1 pending
        $this->assertCount(3, $activeReservations);
        
        // Check that the correct reservations are returned
        $this->assertTrue($activeReservations->contains('id', $activeScheduled->id));
        $this->assertTrue($activeReservations->contains('id', $activeOnDemand->id));
        $this->assertTrue($activeReservations->contains('id', $pendingReservation->id));
        
        // Check that the relationships are loaded
        $this->assertTrue($activeReservations->first()->relationLoaded('parkingSpot'));
        $this->assertTrue($activeReservations->first()->parkingSpot->relationLoaded('facility'));
    }
} 