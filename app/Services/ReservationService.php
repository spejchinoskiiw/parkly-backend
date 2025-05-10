<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReservationType;
use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    /**
     * Get available spots with their time slots for a specific facility and date.
     * 
     * @param int $facilityId The facility ID
     * @param Carbon $date The date to check availability for
     * @return array<int, array<string, mixed>> Array of parking spots with their available time slots and all_day flag
     */
    public function getAvailableSpotsWithTimeSlots(int $facilityId, Carbon $date): array
    {
        // Set the work day start and end times (8am to 5pm)
        $workDayStart = Carbon::parse($date->format('Y-m-d') . ' 08:00:00');
        $workDayEnd = Carbon::parse($date->format('Y-m-d') . ' 17:00:00');
        
        // Get all parking spots for the specified facility
        $parkingSpots = ParkingSpot::where('facility_id', $facilityId)->get();
        
        // Initialize the result array
        $availableSpotsWithTimeSlots = [];
        
        foreach ($parkingSpots as $parkingSpot) {
            // Get all reservations for this parking spot on the specified date
            $reservations = Reservation::where('parking_spot_id', $parkingSpot->id)
                ->where(function (Builder $query) use ($workDayStart, $workDayEnd) {
                    // Get reservations that overlap with the work day
                    $query->whereBetween('start_time', [$workDayStart, $workDayEnd])
                        ->orWhereBetween('end_time', [$workDayStart, $workDayEnd])
                        ->orWhere(function (Builder $query) use ($workDayStart, $workDayEnd) {
                            // Get reservations that completely contain the work day
                            $query->where('start_time', '<=', $workDayStart)
                                ->where(function (Builder $query) use ($workDayEnd) {
                                    $query->where('end_time', '>=', $workDayEnd)
                                        ->orWhere('end_time', null);
                                });
                        });
                })
                ->orderBy('start_time')
                ->get();
            
            // Calculate available time slots based on reservations
            $availableTimeSlots = $this->calculateAvailableTimeSlots($workDayStart, $workDayEnd, $reservations);
            
            // Add to result if there are available time slots
            if (!empty($availableTimeSlots)) {
                // Check if there's a single time slot covering the entire work day
                $isAllDay = count($availableTimeSlots) === 1 && 
                            $availableTimeSlots[0]['start'] === $workDayStart->format('Y-m-d H:i:s') && 
                            $availableTimeSlots[0]['end'] === $workDayEnd->format('Y-m-d H:i:s');
                
                $availableSpotsWithTimeSlots[$parkingSpot->spot_number] = [
                    'parking_spot_id' => $parkingSpot->id,
                    'time_slots' => $availableTimeSlots,
                    'all_day' => $isAllDay,
                ];
            }
        }
        
        return $availableSpotsWithTimeSlots;
    }
    
    /**
     * Calculate available time slots based on existing reservations.
     * 
     * @param Carbon $workDayStart The start of the work day
     * @param Carbon $workDayEnd The end of the work day
     * @param Collection $reservations Collection of reservations
     * @return array<array<string, string>> Array of available time slots
     */
    private function calculateAvailableTimeSlots(Carbon $workDayStart, Carbon $workDayEnd, Collection $reservations): array
    {
        // If there are no reservations, the entire work day is available
        if ($reservations->isEmpty()) {
            return [
                [
                    'start' => $workDayStart->format('Y-m-d H:i:s'),
                    'end' => $workDayEnd->format('Y-m-d H:i:s'),
                ]
            ];
        }
        
        $availableSlots = [];
        $currentTime = $workDayStart->copy();
        
        foreach ($reservations as $reservation) {
            $reservationStart = max($workDayStart->copy(), new Carbon($reservation->start_time));
            
            // If there's a gap between current time and reservation start, add it as an available slot
            if ($currentTime->lt($reservationStart)) {
                $availableSlots[] = [
                    'start' => $currentTime->format('Y-m-d H:i:s'),
                    'end' => $reservationStart->format('Y-m-d H:i:s'),
                ];
            }
            
            // Move current time to the end of this reservation
            if ($reservation->end_time) {
                $currentTime = max($currentTime, new Carbon($reservation->end_time));
            } else {
                // For on-demand reservations, assume they block until the end of the day
                $currentTime = $workDayEnd->copy();
            }
        }
        
        // Add any remaining time until the end of the work day
        if ($currentTime->lt($workDayEnd)) {
            $availableSlots[] = [
                'start' => $currentTime->format('Y-m-d H:i:s'),
                'end' => $workDayEnd->format('Y-m-d H:i:s'),
            ];
        }
        
        return $availableSlots;
    }

    /**
     * Get all reservations for a user on a specific date.
     * 
     * @param User $user The user to get reservations for
     * @param Carbon $date The date to get reservations for
     * @return Collection<int, Reservation> Collection of user's reservations for the date
     */
    public function getUserReservationsForDate(User $user, Carbon $date): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        return Reservation::where('user_id', $user->id)
            ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                // Get reservations that start on the given date
                $query->whereBetween('start_time', [$startOfDay, $endOfDay])
                    // Or end on the given date
                    ->orWhereBetween('end_time', [$startOfDay, $endOfDay])
                    // Or span across the given date
                    ->orWhere(function (Builder $query) use ($startOfDay, $endOfDay) {
                        $query->where('start_time', '<', $startOfDay)
                            ->where(function (Builder $query) use ($endOfDay) {
                                $query->where('end_time', '>', $endOfDay)
                                    ->orWhereNull('end_time');
                            });
                    });
            })
            ->with(['parkingSpot.facility'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Checkout (end) an active reservation for a user and parking spot.
     * 
     * @param User $user The user who is checking out
     * @param int $parkingSpotId The ID of the parking spot to checkout from
     * @return Reservation|null The updated reservation if successful, null if no active reservation found
     */
    public function checkoutReservation(User $user, int $parkingSpotId): ?Reservation
    {
        // Find an active reservation for the user and parking spot
        $reservation = Reservation::where('user_id', $user->id)
            ->where('parking_spot_id', $parkingSpotId)
            ->where(function (Builder $query) {
                $now = Carbon::now();
                
                // Get reservations where:
                // 1. Current time is between start and end times (for scheduled reservations)
                $query->where(function (Builder $query) use ($now) {
                    $query->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                })
                // 2. OR it's an on-demand reservation that started but hasn't ended
                ->orWhere(function (Builder $query) use ($now) {
                    $query->where('start_time', '<=', $now)
                        ->whereNull('end_time')
                        ->where('type', ReservationType::ONDEMAND);
                });
            })
            ->first();
        
        if (!$reservation) {
            return null;
        }
        
        // Set the end time to now for the reservation
        $reservation->end_time = Carbon::now();
        $reservation->save();
        
        return $reservation;
    }

    /**
     * Get all reservations for a specific date (admin only).
     * 
     * @param Carbon $date The date to get reservations for
     * @return Collection<int, Reservation> Collection of all reservations for the date
     */
    public function getAllReservationsForDate(Carbon $date): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        return Reservation::query()
            ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                // Get reservations that start on the given date
                $query->whereBetween('start_time', [$startOfDay, $endOfDay])
                    // Or end on the given date
                    ->orWhereBetween('end_time', [$startOfDay, $endOfDay])
                    // Or span across the given date
                    ->orWhere(function (Builder $query) use ($startOfDay, $endOfDay) {
                        $query->where('start_time', '<', $startOfDay)
                            ->where(function (Builder $query) use ($endOfDay) {
                                $query->where('end_time', '>', $endOfDay)
                                    ->orWhereNull('end_time');
                            });
                    });
            })
            ->with(['parkingSpot.facility', 'user'])
            ->orderBy('start_time')
            ->get();
    }
    
    /**
     * Get all reservations for a specific facility on a date (manager only).
     * 
     * @param int $facilityId The facility ID
     * @param Carbon $date The date to get reservations for
     * @return Collection<int, Reservation> Collection of facility reservations for the date
     */
    public function getFacilityReservationsForDate(int $facilityId, Carbon $date): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        return Reservation::query()
            ->whereHas('parkingSpot', function (Builder $query) use ($facilityId) {
                $query->where('facility_id', $facilityId);
            })
            ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                // Get reservations that start on the given date
                $query->whereBetween('start_time', [$startOfDay, $endOfDay])
                    // Or end on the given date
                    ->orWhereBetween('end_time', [$startOfDay, $endOfDay])
                    // Or span across the given date
                    ->orWhere(function (Builder $query) use ($startOfDay, $endOfDay) {
                        $query->where('start_time', '<', $startOfDay)
                            ->where(function (Builder $query) use ($endOfDay) {
                                $query->where('end_time', '>', $endOfDay)
                                    ->orWhereNull('end_time');
                            });
                    });
            })
            ->with(['parkingSpot.facility', 'user'])
            ->orderBy('start_time')
            ->get();
    }
    
    /**
     * Get an active reservation for a specific parking spot.
     * 
     * @param int $parkingSpotId The ID of the parking spot
     * @return Reservation|null The active reservation if found, null otherwise
     */
    public function getActiveReservationForSpot(int $parkingSpotId): ?Reservation
    {
        $now = Carbon::now();
        
        return Reservation::where('parking_spot_id', $parkingSpotId)
            ->where(function (Builder $query) use ($now) {
                // Get reservations where:
                // 1. Current time is between start and end times (for scheduled reservations)
                $query->where(function (Builder $query) use ($now) {
                    $query->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                })
                // 2. OR it's an on-demand reservation that started but hasn't ended
                ->orWhere(function (Builder $query) use ($now) {
                    $query->where('start_time', '<=', $now)
                        ->whereNull('end_time')
                        ->where('type', ReservationType::ONDEMAND);
                });
            })
            ->first();
    }
    
    /**
     * Checkout (end) an active reservation for a specific parking spot.
     * This is used by admins and managers to check out any reservation.
     * 
     * @param int $parkingSpotId The ID of the parking spot to checkout from
     * @return Reservation|null The updated reservation if successful, null if no active reservation found
     */
    public function checkoutReservationBySpot(int $parkingSpotId): ?Reservation
    {
        $reservation = $this->getActiveReservationForSpot($parkingSpotId);
        
        if (!$reservation) {
            return null;
        }
        
        // Set the end time to now for the reservation
        $reservation->end_time = Carbon::now();
        $reservation->save();
        
        return $reservation;
    }

    /**
     * Update an existing reservation.
     * 
     * @param Reservation $reservation The reservation to update
     * @param array<string, mixed> $data The data to update (start_time, end_time)
     * @return Reservation|null The updated reservation or null if update failed
     */
    public function updateReservation(Reservation $reservation, array $data): ?Reservation
    {
        $startTime = isset($data['start_time']) ? Carbon::parse($data['start_time']) : $reservation->start_time;
        $endTime = isset($data['end_time']) ? Carbon::parse($data['end_time']) : $reservation->end_time;
        
        // For on-demand reservations, we might not have an end time
        if ($reservation->type === ReservationType::ONDEMAND && !isset($data['end_time'])) {
            $endTime = null;
        }
        
        // If we have an end time, validate that it's after the start time
        if ($endTime && $endTime->lessThanOrEqualTo($startTime)) {
            return null;
        }
        
        // Check if the spot is available for the new time period (excluding the current reservation)
        if (!$this->isParkingSpotAvailableForUpdate(
            $reservation->parking_spot_id,
            $startTime,
            $endTime,
            $reservation->id
        )) {
            return null;
        }
        
        return DB::transaction(function () use ($reservation, $startTime, $endTime) {
            $reservation->start_time = $startTime;
            $reservation->end_time = $endTime;
            $reservation->save();
            
            return $reservation;
        });
    }
    
    /**
     * Check if a parking spot is available for the specified time period, excluding a specific reservation.
     * 
     * @param int $parkingSpotId The parking spot ID
     * @param Carbon $startTime The start time to check
     * @param Carbon|null $endTime The end time to check (null for on-demand reservations)
     * @param int $excludeReservationId The ID of the reservation to exclude from the check
     * @return bool True if the spot is available, false otherwise
     */
    private function isParkingSpotAvailableForUpdate(
        int $parkingSpotId,
        Carbon $startTime,
        ?Carbon $endTime,
        int $excludeReservationId
    ): bool {
        $query = Reservation::where('parking_spot_id', $parkingSpotId)
            ->where('id', '!=', $excludeReservationId); // Exclude the current reservation
        
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

    /**
     * Get all active and pending reservations for a user.
     * Active reservations: Current time is between start and end time or on-demand reservations that have started but not ended.
     * Pending reservations: Reservations with a start time in the future.
     * 
     * @param User $user The user to get reservations for
     * @return Collection<int, Reservation> Collection of user's active and pending reservations
     */
    public function getUserActiveReservations(User $user): Collection
    {
        $now = Carbon::now();
        
        return Reservation::where('user_id', $user->id)
            ->where(function (Builder $query) use ($now) {
                // Active reservations: current time is between start and end times
                $query->where(function (Builder $query) use ($now) {
                    $query->where('start_time', '<=', $now)
                        ->where(function (Builder $query) use ($now) {
                            $query->where('end_time', '>=', $now)
                                ->orWhereNull('end_time');
                        });
                })
                // Pending reservations: start time is in the future
                ->orWhere('start_time', '>', $now);
            })
            ->with(['parkingSpot.facility'])
            ->orderBy('start_time')
            ->get();
    }
} 