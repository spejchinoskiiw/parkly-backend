<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReservationType;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class ReservationService
{
    /**
     * Create an on-demand reservation (start time only).
     * 
     * @param User $user The user making the reservation
     * @param ParkingSpot $parkingSpot The parking spot to reserve
     * @param Carbon $startTime The start time for the reservation
     * @return Reservation|null The created reservation or null if creation failed
     */
    public function createOnDemandReservation(User $user, ParkingSpot $parkingSpot, Carbon $startTime): ?Reservation
    {
        // Check if the parking spot is available
        if (!$this->isParkingSpotAvailable($parkingSpot->id, $startTime, null)) {
            return null;
        }
        
        return DB::transaction(function () use ($user, $parkingSpot, $startTime) {
            return Reservation::create([
                'user_id' => $user->id,
                'parking_spot_id' => $parkingSpot->id,
                'start_time' => $startTime,
                'end_time' => null,
                'type' => ReservationType::ONDEMAND,
            ]);
        });
    }
    
    /**
     * Create a scheduled reservation (start and end time).
     * 
     * @param User $user The user making the reservation
     * @param ParkingSpot $parkingSpot The parking spot to reserve
     * @param Carbon $startTime The start time for the reservation
     * @param Carbon $endTime The end time for the reservation
     * @return Reservation|null The created reservation or null if creation failed
     */
    public function createScheduledReservation(User $user, ParkingSpot $parkingSpot, Carbon $startTime, Carbon $endTime): ?Reservation
    {
        // Validate that end time is after start time
        if ($endTime->lessThanOrEqualTo($startTime)) {
            return null;
        }
        
        // Check if the parking spot is available for the entire duration
        if (!$this->isParkingSpotAvailable($parkingSpot->id, $startTime, $endTime)) {
            return null;
        }
        
        return DB::transaction(function () use ($user, $parkingSpot, $startTime, $endTime) {
            return Reservation::create([
                'user_id' => $user->id,
                'parking_spot_id' => $parkingSpot->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'type' => ReservationType::SCHEDULED,
            ]);
        });
    }
    
    /**
     * Check if a parking spot is available for the specified time period.
     * 
     * @param int $parkingSpotId The parking spot ID
     * @param Carbon $startTime The start time to check
     * @param Carbon|null $endTime The end time to check (null for on-demand reservations)
     * @return bool True if the spot is available, false otherwise
     */
    private function isParkingSpotAvailable(int $parkingSpotId, Carbon $startTime, ?Carbon $endTime): bool
    {
        $query = Reservation::where('parking_spot_id', $parkingSpotId);
        
        if ($endTime) {
            // For scheduled reservations, check if there's any overlap with existing reservations
            $query->where(function (Builder $query) use ($startTime, $endTime) {
                // Check for any reservation that overlaps with the requested time period
                $query->where(function (Builder $query) use ($startTime, $endTime) {
                    // Case 1: Existing reservation starts during our time period
                    $query->whereBetween('start_time', [$startTime, $endTime]);
                })->orWhere(function (Builder $query) use ($startTime, $endTime) {
                    // Case 2: Existing reservation ends during our time period
                    $query->where('end_time', '!=', null)
                        ->whereBetween('end_time', [$startTime, $endTime]);
                })->orWhere(function (Builder $query) use ($startTime, $endTime) {
                    // Case 3: Our time period falls entirely within an existing reservation
                    $query->where('start_time', '<=', $startTime)
                        ->where(function (Builder $query) use ($endTime) {
                            $query->where('end_time', '>=', $endTime)
                                ->orWhere('end_time', null);
                        });
                });
            });
        } else {
            // For on-demand reservations, check if there's any reservation at that exact start time
            // or if there's a scheduled reservation that contains this start time
            $query->where(function (Builder $query) use ($startTime) {
                // Case 1: Exact match on start time
                $query->where('start_time', $startTime);
            })->orWhere(function (Builder $query) use ($startTime) {
                // Case 2: Start time falls within a scheduled reservation's time range
                $query->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $startTime);
            });
        }
        
        // If any reservation exists for this time period, the spot is not available
        return !$query->exists();
    }
} 