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
        
        // Create users first (before facilities)
        $users = $this->createBaseUsers();
        
        // Create facilities with assigned managers
        $facilities = $this->createFacilities($users);
        
        // Update users with their facility assignments
        $this->assignUserFacilities($users, $facilities);
        
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
     * Create base users with different roles (without facility assignment yet).
     * 
     * @return array<string, User>
     */
    private function createBaseUsers(): array
    {
        $this->command->info('Creating base users...');
        
        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@iwconnect.com',
            'role' => UserRole::ADMIN->value,
        ]);
        
        // Create manager users for each facility
        $skopjeManager = User::create([
            'name' => 'Skopje Manager',
            'email' => 'skopje.manager@iwconnect.com',
            'role' => UserRole::MANAGER->value,
        ]);
        
        $bitolaManager = User::create([
            'name' => 'Bitola Manager',
            'email' => 'bitola.manager@iwconnect.com',
            'role' => UserRole::MANAGER->value,
        ]);
        
        $prilepManager = User::create([
            'name' => 'Prilep Manager',
            'email' => 'prilep.manager@iwconnect.com',
            'role' => UserRole::MANAGER->value,
        ]);
        
        // Create regular user
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@iwconnect.com',
            'role' => UserRole::USER->value,
        ]);
        
        return [
            'adminUser' => $adminUser,
            'skopjeManager' => $skopjeManager,
            'bitolaManager' => $bitolaManager,
            'prilepManager' => $prilepManager,
            'regularUser' => $regularUser,
        ];
    }
    
    /**
     * Create facilities with assigned managers.
     * 
     * @param array<string, User> $users
     * @return array<string, Facility>
     */
    private function createFacilities(array $users): array
    {
        $this->command->info('Creating facilities with managers...');
        
        $facilities = [];
        
        // Create Skopje facility with its manager
        $facilities['skopje'] = Facility::create([
            'name' => 'Skopje',
            'parking_spot_count' => 10,
            'manager_id' => $users['skopjeManager']->id,
        ]);
        
        // Create Bitola facility with its manager
        $facilities['bitola'] = Facility::create([
            'name' => 'Bitola',
            'parking_spot_count' => 10,
            'manager_id' => $users['bitolaManager']->id,
        ]);
        
        // Create Prilep facility with its manager
        $facilities['prilep'] = Facility::create([
            'name' => 'Prilep',
            'parking_spot_count' => 10,
            'manager_id' => $users['prilepManager']->id,
        ]);
        
        return $facilities;
    }
    
    /**
     * Assign facilities to users.
     * 
     * @param array<string, User> $users
     * @param array<string, Facility> $facilities
     * @return void
     */
    private function assignUserFacilities(array $users, array $facilities): void
    {
        $this->command->info('Assigning users to facilities...');
        
        // Assign admin to Skopje facility
        $users['adminUser']->update(['facility_id' => $facilities['skopje']->id]);
        
        // Assign managers to their facilities
        $users['skopjeManager']->update(['facility_id' => $facilities['skopje']->id]);
        $users['bitolaManager']->update(['facility_id' => $facilities['bitola']->id]);
        $users['prilepManager']->update(['facility_id' => $facilities['prilep']->id]);
        
        // Assign regular user to Prilep facility
        $users['regularUser']->update(['facility_id' => $facilities['prilep']->id]);
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