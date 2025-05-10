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

class ReservationTest extends TestCase
{
    use RefreshDatabase;
    
    private User $user;
    private ParkingSpot $parkingSpot;
    
    protected function setUp(): void
    {
        parent::setUp();
        
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
    
    public function testCreateOnDemandReservation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/ondemand', [
                'parking_spot_id' => $this->parkingSpot->id,
                'start_time' => Carbon::now()->addHour()->toDateTimeString(),
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'parking_spot_id',
                    'start_time',
                    'type',
                ],
            ]);
        
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'type' => ReservationType::ONDEMAND->value,
        ]);
    }
    
    public function testCreateOnDemandReservationFailsWhenSpotAlreadyReserved(): void
    {
        $startTime = Carbon::now()->addHour();
        
        // Create an existing reservation
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $startTime,
            'type' => ReservationType::ONDEMAND,
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/ondemand', [
                'parking_spot_id' => $this->parkingSpot->id,
                'start_time' => $startTime->toDateTimeString(),
            ]);
        
        $response->assertStatus(422);
    }
    
    public function testCreateScheduledReservation(): void
    {
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHours(3);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'parking_spot_id',
                    'start_time',
                    'end_time',
                    'type',
                ],
            ]);
        
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'type' => ReservationType::SCHEDULED->value,
        ]);
    }
    
    public function testCreateScheduledReservationFailsWithInvalidTimeRange(): void
    {
        $startTime = Carbon::now()->addHour();
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $startTime->toDateTimeString(), // Same time, should fail
            ]);
        
        $response->assertStatus(422);
    }
    
    public function testCreateScheduledReservationFailsWhenOverlapping(): void
    {
        $existingStartTime = Carbon::now()->addHour();
        $existingEndTime = Carbon::now()->addHours(3);
        
        // Create an existing reservation
        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => $existingStartTime,
            'end_time' => $existingEndTime,
            'type' => ReservationType::SCHEDULED,
        ]);
        
        // Try to create an overlapping reservation
        $newStartTime = Carbon::now()->addHours(2);
        $newEndTime = Carbon::now()->addHours(4);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot->id,
                'start_time' => $newStartTime->toDateTimeString(),
                'end_time' => $newEndTime->toDateTimeString(),
            ]);
        
        $response->assertStatus(422);
    }
    
    public function testUnauthenticatedUserCannotCreateReservations(): void
    {
        $response = $this->postJson('/api/reservations/ondemand', [
            'parking_spot_id' => $this->parkingSpot->id,
            'start_time' => Carbon::now()->addHour()->toDateTimeString(),
        ]);
        
        $response->assertStatus(401);
    }
} 