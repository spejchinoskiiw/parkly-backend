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
} 