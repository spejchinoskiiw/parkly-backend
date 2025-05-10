<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\User;
use App\Services\FacilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class FacilityServiceTest extends TestCase
{
    use RefreshDatabase;
    
    private FacilityService $facilityService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->facilityService = new FacilityService();
    }
    
    public function testCreateFacility(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $data = [
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
            'manager_id' => null,
        ];
        
        // Act
        $facility = $this->facilityService->createFacility($data);
        
        // Assert
        $this->assertDatabaseHas('facilities', [
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
        ]);
        
        // Check if parking spots were created
        $this->assertEquals(10, $facility->parkingSpots()->count());
        
        // Check if spot numbers are sequential
        for ($i = 1; $i <= 10; $i++) {
            $this->assertDatabaseHas('parking_spots', [
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
    }
    
    public function testCreateFacilityWithManager(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Create a manager directly in the database
        $manager_id = 999;
        \DB::table('users')->insert([
            'id' => $manager_id,
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
        ]);
        
        $data = [
            'name' => 'Manager Facility',
            'parking_spot_count' => 5,
            'manager_id' => $manager_id,
        ];
        
        // Act
        $facility = $this->facilityService->createFacility($data);
        
        // Assert
        $this->assertDatabaseHas('facilities', [
            'name' => 'Manager Facility',
            'parking_spot_count' => 5,
            'manager_id' => $manager_id,
        ]);
        
        $this->assertEquals(5, $facility->parkingSpots()->count());
    }
    
    public function testUpdateFacilityIncreaseSpotCount(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Original Facility',
            'parking_spot_count' => 3,
        ]);
        
        // Create initial parking spots
        for ($i = 1; $i <= 3; $i++) {
            ParkingSpot::factory()->create([
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
        
        $data = [
            'name' => 'Updated Facility',
            'parking_spot_count' => 5,
        ];
        
        // Act
        $updatedFacility = $this->facilityService->updateFacility($facility, $data);
        
        // Assert
        $this->assertEquals('Updated Facility', $updatedFacility->name);
        $this->assertEquals(5, $updatedFacility->parking_spot_count);
        
        // Check that we now have 5 parking spots
        $this->assertEquals(5, $updatedFacility->parkingSpots()->count());
        
        // Check if the new spots were added
        for ($i = 4; $i <= 5; $i++) {
            $this->assertDatabaseHas('parking_spots', [
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
    }
    
    public function testUpdateFacilityDecreaseSpotCount(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Original Facility',
            'parking_spot_count' => 5,
        ]);
        
        // Create initial parking spots
        for ($i = 1; $i <= 5; $i++) {
            ParkingSpot::factory()->create([
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
        
        $data = [
            'parking_spot_count' => 3,
        ];
        
        // Act
        $updatedFacility = $this->facilityService->updateFacility($facility, $data);
        
        // Assert
        $this->assertEquals(3, $updatedFacility->parking_spot_count);
        
        // Check that we now have 3 parking spots
        $this->assertEquals(3, $updatedFacility->parkingSpots()->count());
        
        // Check if the highest numbered spots were removed
        for ($i = 4; $i <= 5; $i++) {
            $this->assertDatabaseMissing('parking_spots', [
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
        
        // Check if the lowest numbered spots still exist
        for ($i = 1; $i <= 3; $i++) {
            $this->assertDatabaseHas('parking_spots', [
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
    }
    
    public function testDeleteFacility(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Facility to Delete',
            'parking_spot_count' => 3,
        ]);
        
        // Create parking spots
        for ($i = 1; $i <= 3; $i++) {
            ParkingSpot::factory()->create([
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
        
        // Act
        $result = $this->facilityService->deleteFacility($facility);
        
        // Assert
        $this->assertTrue($result);
        
        // Check that the facility was deleted
        $this->assertDatabaseMissing('facilities', [
            'id' => $facility->id,
        ]);
        
        // Check that all associated parking spots were deleted (cascade)
        for ($i = 1; $i <= 3; $i++) {
            $this->assertDatabaseMissing('parking_spots', [
                'facility_id' => $facility->id,
                'spot_number' => $i,
            ]);
        }
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
