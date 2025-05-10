<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateOnDemandReservationRequest;
use App\Http\Requests\CreateScheduledReservationRequest;
use App\Models\ParkingSpot;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService
    ) {
    }
    
    /**
     * Create an on-demand reservation.
     * 
     * This endpoint is documented in:
     * @see \App\Http\Controllers\Api\Annotations\ReservationAnnotations::createOnDemand()
     * 
     * @param CreateOnDemandReservationRequest $request
     * @return JsonResponse
     */
    public function createOnDemand(CreateOnDemandReservationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $parkingSpot = ParkingSpot::findOrFail($validatedData['parking_spot_id']);
        $startTime = Carbon::parse($validatedData['start_time']);
        
        $reservation = $this->reservationService->createOnDemandReservation(
            $request->user(),
            $parkingSpot,
            $startTime
        );
        
        if (!$reservation) {
            return response()->json([
                'message' => 'Parking spot is not available for this time',
            ], 422);
        }
        
        return response()->json([
            'message' => 'Reservation created successfully',
            'data' => $reservation,
        ], 201);
    }
    
    /**
     * Create a scheduled reservation.
     * 
     * This endpoint is documented in:
     * @see \App\Http\Controllers\Api\Annotations\ReservationAnnotations::createScheduled()
     * 
     * @param CreateScheduledReservationRequest $request
     * @return JsonResponse
     */
    public function createScheduled(CreateScheduledReservationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $parkingSpot = ParkingSpot::findOrFail($validatedData['parking_spot_id']);
        $startTime = Carbon::parse($validatedData['start_time']);
        $endTime = Carbon::parse($validatedData['end_time']);
        
        $reservation = $this->reservationService->createScheduledReservation(
            $request->user(),
            $parkingSpot,
            $startTime,
            $endTime
        );
        
        if (!$reservation) {
            return response()->json([
                'message' => 'Parking spot is not available for this time period or invalid time range',
            ], 422);
        }
        
        return response()->json([
            'message' => 'Reservation created successfully',
            'data' => $reservation,
        ], 201);
    }
} 