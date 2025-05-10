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

class ReservationAvailabilitySpotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_available_spots(): void
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a facility
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 5,
        ]);
        
        // Create some parking spots
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
            'user_id' => $user->id,
            'parking_spot_id' => $spot1->id,
            'start_time' => Carbon::parse('2023-05-20 13:00:00'),
            'end_time' => Carbon::parse('2023-05-20 15:00:00'),
            'type' => 'scheduled',
        ]);
        
        // Create a response and check the response
        $response = $this->actingAs($user)
            ->getJson('/api/available-spots?facility_id=' . $facility->id . '&date=2023-05-20');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '1' => ['time_slots', 'all_day'],
                    '2' => ['time_slots', 'all_day'],
                ],
            ]);
        
        // Verify that spot 1 has two time slots: 8am-1pm and 3pm-5pm and is not all day
        $this->assertEquals(2, count($response->json('data.1.time_slots')));
        $this->assertEquals($workStart->format('Y-m-d H:i:s'), $response->json('data.1.time_slots.0.start'));
        $this->assertEquals(Carbon::parse('2023-05-20 13:00:00')->format('Y-m-d H:i:s'), $response->json('data.1.time_slots.0.end'));
        $this->assertEquals(Carbon::parse('2023-05-20 15:00:00')->format('Y-m-d H:i:s'), $response->json('data.1.time_slots.1.start'));
        $this->assertEquals($workEnd->format('Y-m-d H:i:s'), $response->json('data.1.time_slots.1.end'));
        $this->assertFalse($response->json('data.1.all_day'));
        
        // Verify that spot 2 has one time slot: the entire day and is marked as all day
        $this->assertEquals(1, count($response->json('data.2.time_slots')));
        $this->assertEquals($workStart->format('Y-m-d H:i:s'), $response->json('data.2.time_slots.0.start'));
        $this->assertEquals($workEnd->format('Y-m-d H:i:s'), $response->json('data.2.time_slots.0.end'));
        $this->assertTrue($response->json('data.2.all_day'));
    }
    
    public function test_validation_errors_are_returned(): void
    {
        $user = User::factory()->create();
        
        // Test with missing facility_id
        $response = $this->actingAs($user)
            ->getJson('/api/available-spots?date=2023-05-20');
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['facility_id']);
        
        // Test with missing date
        $response = $this->actingAs($user)
            ->getJson('/api/available-spots?facility_id=1');
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
        
        // Test with invalid date format
        $response = $this->actingAs($user)
            ->getJson('/api/available-spots?facility_id=1&date=20-05-2023');
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
        
        // Test with non-existent facility
        $response = $this->actingAs($user)
            ->getJson('/api/available-spots?facility_id=999&date=2023-05-20');
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['facility_id']);
    }
} 