<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Services\ParkingSpotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ParkingSpotServiceTest extends TestCase
{
    use RefreshDatabase;
    
    private ParkingSpotService $parkingSpotService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->parkingSpotService = new ParkingSpotService();
    }
    
    public function testCreateParkingSpot(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
        ]);
        
        $data = [
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ];
        
        // Act
        $parkingSpot = $this->parkingSpotService->createParkingSpot($data);
        
        // Assert
        $this->assertDatabaseHas('parking_spots', [
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        $this->assertEquals($facility->id, $parkingSpot->facility_id);
        $this->assertEquals(1, $parkingSpot->spot_number);
    }
    
    public function testCreateParkingSpotFailsWithDuplicateSpotNumber(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
        ]);
        
        // Create a parking spot
        ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        $data = [
            'facility_id' => $facility->id,
            'spot_number' => 1, // Same spot number
        ];
        
        // Assert & Act
        $this->expectException(ValidationException::class);
        $this->parkingSpotService->createParkingSpot($data);
    }
    
    public function testCreateParkingSpotFailsWhenReachingMaxSpots(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 2,
        ]);
        
        // Create the maximum number of parking spots
        ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 2,
        ]);
        
        $data = [
            'facility_id' => $facility->id,
            'spot_number' => 3, // Exceeds the max
        ];
        
        // Assert & Act
        $this->expectException(ValidationException::class);
        $this->parkingSpotService->createParkingSpot($data);
    }
    
    public function testUpdateParkingSpot(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
        ]);
        
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        $data = [
            'spot_number' => 5,
        ];
        
        // Act
        $updatedSpot = $this->parkingSpotService->updateParkingSpot($parkingSpot, $data);
        
        // Assert
        $this->assertEquals(5, $updatedSpot->spot_number);
        $this->assertDatabaseHas('parking_spots', [
            'id' => $parkingSpot->id,
            'facility_id' => $facility->id,
            'spot_number' => 5,
        ]);
    }
    
    public function testUpdateParkingSpotFailsWithDuplicateSpotNumber(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
        ]);
        
        // Create two parking spots
        $parkingSpot1 = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 2,
        ]);
        
        $data = [
            'spot_number' => 2, // Try to change to an existing spot number
        ];
        
        // Assert & Act
        $this->expectException(ValidationException::class);
        $this->parkingSpotService->updateParkingSpot($parkingSpot1, $data);
    }
    
    public function testDeleteParkingSpot(): void
    {
        // Run migrations
        $this->artisan('migrate');
        
        // Arrange
        $facility = Facility::factory()->create([
            'name' => 'Test Facility',
            'parking_spot_count' => 10,
        ]);
        
        $parkingSpot = ParkingSpot::factory()->create([
            'facility_id' => $facility->id,
            'spot_number' => 1,
        ]);
        
        // Act
        $result = $this->parkingSpotService->deleteParkingSpot($parkingSpot);
        
        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('parking_spots', [
            'id' => $parkingSpot->id,
        ]);
    }
}
