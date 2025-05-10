<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\ParkingSpot;
use Illuminate\Support\Facades\DB;

final class FacilityService
{
    /**
     * Create a new facility with parking spots.
     */
    public function createFacility(array $data): Facility
    {
        return DB::transaction(function () use ($data) {
            $facility = Facility::create([
                'name' => $data['name'],
                'parking_spot_count' => $data['parking_spot_count'],
                'manager_id' => $data['manager_id'] ?? null,
            ]);

            // Create parking spots for this facility
            $this->createParkingSpots($facility);

            return $facility;
        });
    }

    /**
     * Update a facility.
     */
    public function updateFacility(Facility $facility, array $data): Facility
    {
        return DB::transaction(function () use ($facility, $data) {
            $oldCount = $facility->parking_spot_count;
            $newCount = $data['parking_spot_count'] ?? $oldCount;

            $facility->update([
                'name' => $data['name'] ?? $facility->name,
                'parking_spot_count' => $newCount,
                'manager_id' => $data['manager_id'] ?? $facility->manager_id,
            ]);

            // If parking spot count changed, adjust spots accordingly
            if ($newCount > $oldCount) {
                // Add more parking spots
                $this->addParkingSpots($facility, $oldCount, $newCount);
            } elseif ($newCount < $oldCount) {
                // Remove excess parking spots
                $this->removeParkingSpots($facility, $newCount, $oldCount);
            }

            return $facility;
        });
    }

    /**
     * Delete a facility and its parking spots.
     */
    public function deleteFacility(Facility $facility): bool
    {
        return DB::transaction(function () use ($facility) {
            // Cascade delete will handle parking spots due to foreign key constraint
            return $facility->delete();
        });
    }

    /**
     * Create parking spots for a facility.
     */
    private function createParkingSpots(Facility $facility): void
    {
        $this->addParkingSpots($facility, 0, $facility->parking_spot_count);
    }

    /**
     * Add parking spots to a facility.
     */
    private function addParkingSpots(Facility $facility, int $startFrom, int $totalCount): void
    {
        $spots = [];
        for ($i = $startFrom + 1; $i <= $totalCount; $i++) {
            $spots[] = [
                'facility_id' => $facility->id,
                'spot_number' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($spots)) {
            ParkingSpot::insert($spots);
        }
    }

    /**
     * Remove excess parking spots from a facility.
     */
    private function removeParkingSpots(Facility $facility, int $newCount, int $oldCount): void
    {
        // Remove highest numbered spots first
        $facility->parkingSpots()
            ->where('spot_number', '>', $newCount)
            ->delete();
    }
} 