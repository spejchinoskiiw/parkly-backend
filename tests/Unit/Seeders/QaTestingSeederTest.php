<?php

declare(strict_types=1);

namespace Tests\Unit\Seeders;

use App\Models\Facility;
use App\Models\User;
use Database\Seeders\QaTestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaTestingSeederTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_facilities_are_created_with_managers(): void
    {
        // Run the QA seeder
        $this->seed(QaTestingSeeder::class);
        
        // Verify that all facilities have managers
        $facilities = Facility::all();
        
        $this->assertGreaterThan(0, $facilities->count(), 'No facilities were created by the seeder');
        
        foreach ($facilities as $facility) {
            $this->assertNotNull(
                $facility->manager_id,
                "Facility '{$facility->name}' does not have a manager assigned"
            );
            
            $this->assertNotNull(
                $facility->manager,
                "Facility '{$facility->name}' has an invalid manager relationship"
            );
            
            $this->assertEquals(
                $facility->manager->facility_id,
                $facility->id,
                "Manager for facility '{$facility->name}' is not correctly assigned to the facility"
            );
        }
    }
    
    public function test_facility_managers_have_manager_role(): void
    {
        // Run the QA seeder
        $this->seed(QaTestingSeeder::class);
        
        // Get all facilities with their managers
        $facilities = Facility::with('manager')->get();
        
        foreach ($facilities as $facility) {
            $this->assertEquals(
                'manager',
                $facility->manager->role,
                "The user assigned as manager for facility '{$facility->name}' does not have the manager role"
            );
        }
    }
} 