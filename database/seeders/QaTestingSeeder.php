<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ReservationType;
use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QaTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up existing data
        $this->cleanDatabase();
        
        // Create facilities
        $facilities = $this->createFacilities();
        
        // Create users (one of each role)
        $users = $this->createUsers($facilities);
        
        // Create parking spots (10 per facility)
        $parkingSpots = $this->createParkingSpots($facilities);
        
        // Create a reservation for the regular user
        $this->createReservation($users['regularUser'], $parkingSpots['skopje'][0]);
        
        $this->command->info('QA test data seeded successfully!');
    }
    
    /**
     * Clean the database before seeding.
     */
    private function cleanDatabase(): void
    {
        $this->command->info('Cleaning database...');
        
        // Delete in the correct order to respect foreign key constraints
        Reservation::query()->delete();
        ParkingSpot::query()->delete();
        User::query()->delete();
        Facility::query()->delete();
    }
    
    /**
     * Create facilities: Skopje, Bitola, Prilep.
     * 
     * @return array<string, Facility>
     */
    private function createFacilities(): array
    {
        $this->command->info('Creating facilities...');
        
        $facilityNames = ['Skopje', 'Bitola', 'Prilep'];
        $facilities = [];
        
        foreach ($facilityNames as $name) {
            $facilities[strtolower($name)] = Facility::create([
                'name' => $name,
                'parking_spot_count' => 10,
            ]);
        }
        
        return $facilities;
    }
    
    /**
     * Create users with different roles.
     * 
     * @param array<string, Facility> $facilities
     * @return array<string, User>
     */
    private function createUsers(array $facilities): array
    {
        $this->command->info('Creating users...');
        
        // Create admin user assigned to Skopje
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@iwconnect.com',
            'role' => UserRole::ADMIN->value,
            'facility_id' => $facilities['skopje']->id,
        ]);
        
        // Create manager user assigned to Bitola
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@iwconnect.com',
            'role' => UserRole::MANAGER->value,
            'facility_id' => $facilities['bitola']->id,
        ]);
        
        // Create regular user assigned to Prilep
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@iwconnect.com',
            'role' => UserRole::USER->value,
            'facility_id' => $facilities['prilep']->id,
        ]);
        
        return [
            'adminUser' => $adminUser,
            'managerUser' => $managerUser,
            'regularUser' => $regularUser,
        ];
    }
    
    /**
     * Create 10 parking spots for each facility.
     * 
     * @param array<string, Facility> $facilities
     * @return array<string, array<int, ParkingSpot>>
     */
    private function createParkingSpots(array $facilities): array
    {
        $this->command->info('Creating parking spots...');
        
        $parkingSpots = [];
        
        foreach ($facilities as $key => $facility) {
            $parkingSpots[$key] = [];
            
            for ($i = 1; $i <= 10; $i++) {
                $parkingSpots[$key][] = ParkingSpot::create([
                    'facility_id' => $facility->id,
                    'spot_number' => $i,
                ]);
            }
        }
        
        return $parkingSpots;
    }
    
    /**
     * Create a reservation for a user.
     * 
     * @param User $user
     * @param ParkingSpot $parkingSpot
     * @return Reservation
     */
    private function createReservation(User $user, ParkingSpot $parkingSpot): Reservation
    {
        $this->command->info('Creating reservation...');
        
        // Create a reservation starting tomorrow at 9:00 AM and ending at 5:00 PM
        $startTime = Carbon::tomorrow()->setHour(9)->setMinute(0);
        $endTime = Carbon::tomorrow()->setHour(17)->setMinute(0);
        
        return Reservation::create([
            'user_id' => $user->id,
            'parking_spot_id' => $parkingSpot->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => ReservationType::SCHEDULED->value,
        ]);
    }
} 