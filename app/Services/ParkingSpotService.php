<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\ParkingSpot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ParkingSpotService
{
    /**
     * Create a new parking spot.
     */
    public function createParkingSpot(array $data): ParkingSpot
    {
        return DB::transaction(function () use ($data) {
            $facility = Facility::findOrFail($data['facility_id']);
            
            // Check if spot number already exists
            $exists = ParkingSpot::where([
                'facility_id' => $facility->id,
                'spot_number' => $data['spot_number'],
            ])->exists();
            
            if ($exists) {
                throw ValidationException::withMessages([
                    'spot_number' => ['Parking spot number already exists for this facility.'],
                ]);
            }
            
            // Check if we're exceeding the facility's parking spot count
            $currentCount = $facility->parkingSpots()->count();
            if ($currentCount >= $facility->parking_spot_count) {
                throw ValidationException::withMessages([
                    'facility_id' => ['This facility has reached its maximum parking spot count.'],
                ]);
            }
            
            return ParkingSpot::create([
                'facility_id' => $facility->id,
                'spot_number' => $data['spot_number'],
            ]);
        });
    }

    /**
     * Update a parking spot.
     */
    public function updateParkingSpot(ParkingSpot $parkingSpot, array $data): ParkingSpot
    {
        return DB::transaction(function () use ($parkingSpot, $data) {
            if (isset($data['spot_number']) && $data['spot_number'] !== $parkingSpot->spot_number) {
                // Check if new spot number already exists
                $exists = ParkingSpot::where([
                    'facility_id' => $parkingSpot->facility_id,
                    'spot_number' => $data['spot_number'],
                ])->exists();
                
                if ($exists) {
                    throw ValidationException::withMessages([
                        'spot_number' => ['Parking spot number already exists for this facility.'],
                    ]);
                }
            }
            
            $parkingSpot->update([
                'spot_number' => $data['spot_number'] ?? $parkingSpot->spot_number,
            ]);
            
            return $parkingSpot;
        });
    }

    /**
     * Delete a parking spot.
     */
    public function deleteParkingSpot(ParkingSpot $parkingSpot): bool
    {
        return DB::transaction(function () use ($parkingSpot) {
            return $parkingSpot->delete();
        });
    }
} 