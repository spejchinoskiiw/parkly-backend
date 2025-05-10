<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReservationType;
use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $managerFacility1;
    private User $managerFacility2;
    private User $regularUser1;
    private User $regularUser2;
    private Facility $facility1;
    private Facility $facility2;
    private ParkingSpot $parkingSpot1;
    private ParkingSpot $parkingSpot2;
    private Reservation $reservationUser1;
    private Reservation $reservationUser2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create facilities
        $this->facility1 = Facility::factory()->create([
            'name' => 'Facility 1',
        ]);
        
        $this->facility2 = Facility::factory()->create([
            'name' => 'Facility 2',
        ]);

        // Create users with different roles
        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        $this->managerFacility1 = User::factory()->create([
            'role' => UserRole::MANAGER->value,
            'facility_id' => $this->facility1->id,
        ]);
        
        $this->facility1->manager_id = $this->managerFacility1->id;
        $this->facility1->save();
        
        $this->managerFacility2 = User::factory()->create([
            'role' => UserRole::MANAGER->value,
            'facility_id' => $this->facility2->id,
        ]);
        
        $this->facility2->manager_id = $this->managerFacility2->id;
        $this->facility2->save();

        $this->regularUser1 = User::factory()->create([
            'role' => UserRole::USER->value,
            'facility_id' => $this->facility1->id,
        ]);

        $this->regularUser2 = User::factory()->create([
            'role' => UserRole::USER->value,
            'facility_id' => $this->facility2->id,
        ]);

        // Create parking spots
        $this->parkingSpot1 = ParkingSpot::factory()->create([
            'facility_id' => $this->facility1->id,
        ]);

        $this->parkingSpot2 = ParkingSpot::factory()->create([
            'facility_id' => $this->facility2->id,
        ]);

        // Create reservations
        $startTime = Carbon::now()->addHour();
        $endTime = Carbon::now()->addHours(2);

        $this->reservationUser1 = Reservation::factory()->create([
            'user_id' => $this->regularUser1->id,
            'parking_spot_id' => $this->parkingSpot1->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);

        $this->reservationUser2 = Reservation::factory()->create([
            'user_id' => $this->regularUser2->id,
            'parking_spot_id' => $this->parkingSpot2->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED,
        ]);
    }

    /**
     * Test admin can view all reservations.
     */
    public function test_admin_can_view_all_reservations(): void
    {
        $targetDate = Carbon::today()->format('Y-m-d');
        
        // Test admin can see all reservations
        $response = $this->actingAs($this->admin)
            ->getJson("/api/reservations/reservationsForDate?date={$targetDate}");
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        
        // Admin should see both reservations
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test manager can view reservations for their facility only.
     */
    public function test_manager_can_view_only_their_facility_reservations(): void
    {
        $targetDate = Carbon::today()->format('Y-m-d');
        
        // Test manager of facility 1 can see reservations for facility 1 only
        $response = $this->actingAs($this->managerFacility1)
            ->getJson("/api/reservations/reservationsForDate?date={$targetDate}");
        
        $response->assertStatus(200);
        
        // Manager should only see reservations for their facility
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->reservationUser1->id, $response->json('data.0.id'));
    }

    /**
     * Test regular user can view only their own reservations.
     */
    public function test_regular_user_can_view_only_own_reservations(): void
    {
        $targetDate = Carbon::today()->format('Y-m-d');
        
        // Test regular user 1 can see only their own reservations
        $response = $this->actingAs($this->regularUser1)
            ->getJson("/api/reservations/reservationsForDate?date={$targetDate}");
        
        $response->assertStatus(200);
        
        // User should only see their own reservations
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->reservationUser1->id, $response->json('data.0.id'));
    }

    /**
     * Test admin can create reservation for any spot.
     */
    public function test_admin_can_create_reservation_for_any_spot(): void
    {
        $startTime = Carbon::now()->addHours(3)->format('Y-m-d H:i:s');
        $endTime = Carbon::now()->addHours(4)->format('Y-m-d H:i:s');
        
        // Test admin can create reservation for facility 1
        $response = $this->actingAs($this->admin)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot1->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        
        $response->assertStatus(201);
        
        // Test admin can create reservation for facility 2
        $response = $this->actingAs($this->admin)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot2->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        
        $response->assertStatus(201);
    }

    /**
     * Test manager can create reservation for their facility spots only.
     */
    public function test_manager_can_create_reservation_for_their_facility_only(): void
    {
        $startTime = Carbon::now()->addHours(3)->format('Y-m-d H:i:s');
        $endTime = Carbon::now()->addHours(4)->format('Y-m-d H:i:s');
        
        // Test manager can create reservation for their facility
        $response = $this->actingAs($this->managerFacility1)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot1->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        
        $response->assertStatus(201);
        
        // Test manager cannot create reservation for another facility
        // Note: This will require controller modification to check facility access
        $response = $this->actingAs($this->managerFacility1)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot2->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        
        $response->assertStatus(403);
    }

    /**
     * Test regular user can create reservation for themselves only.
     */
    public function test_regular_user_can_create_reservation_for_themselves(): void
    {
        $startTime = Carbon::now()->addHours(3)->format('Y-m-d H:i:s');
        $endTime = Carbon::now()->addHours(4)->format('Y-m-d H:i:s');
        
        // Test user can create reservation
        $response = $this->actingAs($this->regularUser1)
            ->postJson('/api/reservations/scheduled', [
                'parking_spot_id' => $this->parkingSpot1->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        
        $response->assertStatus(201);
    }

    /**
     * Test admin can update any reservation.
     */
    public function test_admin_can_update_any_reservation(): void
    {
        $newEndTime = Carbon::now()->addHours(5)->format('Y-m-d H:i:s');
        
        // Test admin can update user1's reservation
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/reservations/{$this->reservationUser1->id}", [
                'end_time' => $newEndTime,
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation updated successfully',
            ])
            ->assertJsonPath('data.id', $this->reservationUser1->id);
        
        // Verify the reservation was updated
        $this->reservationUser1->refresh();
        $this->assertEquals($newEndTime, $this->reservationUser1->end_time->format('Y-m-d H:i:s'));
        
        // Test admin can update user2's reservation
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/reservations/{$this->reservationUser2->id}", [
                'end_time' => $newEndTime,
            ]);
        
        $response->assertStatus(200);
    }

    /**
     * Test manager can update reservations for their facility only.
     */
    public function test_manager_can_update_only_their_facility_reservations(): void
    {
        $newEndTime = Carbon::now()->addHours(5)->format('Y-m-d H:i:s');
        
        // Test manager can update reservation for their facility
        $response = $this->actingAs($this->managerFacility1)
            ->patchJson("/api/reservations/{$this->reservationUser1->id}", [
                'end_time' => $newEndTime,
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation updated successfully',
            ]);
        
        // Verify the reservation was updated
        $this->reservationUser1->refresh();
        $this->assertEquals($newEndTime, $this->reservationUser1->end_time->format('Y-m-d H:i:s'));
        
        // Test manager cannot update reservation for another facility
        $response = $this->actingAs($this->managerFacility1)
            ->patchJson("/api/reservations/{$this->reservationUser2->id}", [
                'end_time' => $newEndTime,
            ]);
        
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to update this reservation',
            ]);
    }

    /**
     * Test regular user can update only their own reservations.
     */
    public function test_regular_user_can_update_only_own_reservations(): void
    {
        $newEndTime = Carbon::now()->addHours(5)->format('Y-m-d H:i:s');
        
        // Test user can update their own reservation
        $response = $this->actingAs($this->regularUser1)
            ->patchJson("/api/reservations/{$this->reservationUser1->id}", [
                'end_time' => $newEndTime,
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation updated successfully',
            ]);
        
        // Verify the reservation was updated
        $this->reservationUser1->refresh();
        $this->assertEquals($newEndTime, $this->reservationUser1->end_time->format('Y-m-d H:i:s'));
        
        // Test user cannot update another user's reservation
        $response = $this->actingAs($this->regularUser1)
            ->patchJson("/api/reservations/{$this->reservationUser2->id}", [
                'end_time' => $newEndTime,
            ]);
        
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to update this reservation',
            ]);
    }

    /**
     * Test admin can delete any reservation.
     */
    public function test_admin_can_delete_any_reservation(): void
    {
        // Test admin can delete user1's reservation
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/reservations/{$this->reservationUser1->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation deleted successfully',
            ]);
        
        // Verify the reservation was deleted
        $this->assertDatabaseMissing('reservations', [
            'id' => $this->reservationUser1->id,
        ]);
        
        // Test admin can delete user2's reservation
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/reservations/{$this->reservationUser2->id}");
        
        $response->assertStatus(200);
        
        // Verify the reservation was deleted
        $this->assertDatabaseMissing('reservations', [
            'id' => $this->reservationUser2->id,
        ]);
    }

    /**
     * Test manager can delete reservations for their facility only.
     */
    public function test_manager_can_delete_only_their_facility_reservations(): void
    {
        // Test manager can delete reservation for their facility
        $response = $this->actingAs($this->managerFacility1)
            ->deleteJson("/api/reservations/{$this->reservationUser1->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation deleted successfully',
            ]);
        
        // Verify the reservation was deleted
        $this->assertDatabaseMissing('reservations', [
            'id' => $this->reservationUser1->id,
        ]);
        
        // Test manager cannot delete reservation for another facility
        $response = $this->actingAs($this->managerFacility1)
            ->deleteJson("/api/reservations/{$this->reservationUser2->id}");
        
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to delete this reservation',
            ]);
        
        // Verify the reservation was NOT deleted
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservationUser2->id,
        ]);
    }

    /**
     * Test regular user can delete only their own reservations.
     */
    public function test_regular_user_can_delete_only_own_reservations(): void
    {
        // Test user can delete their own reservation
        $response = $this->actingAs($this->regularUser1)
            ->deleteJson("/api/reservations/{$this->reservationUser1->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reservation deleted successfully',
            ]);
        
        // Verify the reservation was deleted
        $this->assertDatabaseMissing('reservations', [
            'id' => $this->reservationUser1->id,
        ]);
        
        // Test user cannot delete another user's reservation
        $response = $this->actingAs($this->regularUser1)
            ->deleteJson("/api/reservations/{$this->reservationUser2->id}");
        
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to delete this reservation',
            ]);
        
        // Verify the reservation was NOT deleted
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservationUser2->id,
        ]);
    }
    
    /**
     * Test admin can checkout any reservation.
     */
    public function test_admin_can_checkout_any_reservation(): void
    {
        // First make the reservations currently active
        $now = Carbon::now();
        
        $this->reservationUser1->update([
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
        ]);
        
        $this->reservationUser2->update([
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
        ]);
        
        // Test admin can checkout user1's reservation
        $response = $this->actingAs($this->admin)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $this->parkingSpot1->id,
            ]);
        
        $response->assertStatus(200);
        
        // Refresh
        $this->reservationUser1->refresh();
        $this->assertNotNull($this->reservationUser1->end_time);
    }
    
    /**
     * Test manager can checkout reservations for their facility only.
     */
    public function test_manager_can_checkout_only_their_facility_reservations(): void
    {
        // First make the reservations currently active
        $now = Carbon::now();
        
        $this->reservationUser1->update([
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
        ]);
        
        $this->reservationUser2->update([
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
        ]);
        
        // Test manager1 can checkout facility1 reservation
        $response = $this->actingAs($this->managerFacility1)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $this->parkingSpot1->id,
            ]);
        
        $response->assertStatus(200);
        
        // Test manager1 cannot checkout facility2 reservation
        // Note: This requires controller modification to check facility access
        $response = $this->actingAs($this->managerFacility1)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $this->parkingSpot2->id,
            ]);
        
        $response->assertStatus(403);
    }
    
    /**
     * Test regular user can checkout only their own reservations.
     */
    public function test_regular_user_can_checkout_only_own_reservations(): void
    {
        // First make the reservations currently active
        $now = Carbon::now();
        
        $this->reservationUser1->update([
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
        ]);
        
        $this->reservationUser2->update([
            'start_time' => $now->copy()->subHour(),
            'end_time' => $now->copy()->addHour(),
        ]);
        
        // Test user1 can checkout their own reservation
        $response = $this->actingAs($this->regularUser1)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $this->parkingSpot1->id,
            ]);
        
        $response->assertStatus(200);
        
        // Test user1 cannot checkout user2's reservation
        $response = $this->actingAs($this->regularUser1)
            ->postJson('/api/reservations/checkout', [
                'parking_spot_id' => $this->parkingSpot2->id,
            ]);
        
        // Should be 404 because the reservation doesn't exist for user1
        $response->assertStatus(404);
    }
}
