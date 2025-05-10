<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateOnDemandReservationRequest;
use App\Http\Requests\CreateScheduledReservationRequest;
use App\Http\Requests\GetAvailableSpotsRequest;
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

    /**
     * Get available parking spots with time slots for a specific date and facility.
     * 
     * @OA\Get(
     *     path="/api/available-spots",
     *     summary="Get available parking spots with time slots",
     *     description="Returns a list of available parking spots with their available time slots for a specific date and facility",
     *     operationId="getAvailableSpots",
     *     tags={"Reservations"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="facility_id",
     *         in="query",
     *         description="ID of the facility",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date to check availability for (format: Y-m-d)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Available spots with time slots",
     *                 example={
     *                     "1": {
     *                         {"start": "2023-05-20 08:00:00", "end": "2023-05-20 13:00:00"},
     *                         {"start": "2023-05-20 15:00:00", "end": "2023-05-20 17:00:00"}
     *                     },
     *                     "2": {
     *                         {"start": "2023-05-20 08:00:00", "end": "2023-05-20 17:00:00"}
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="facility_id", type="array", @OA\Items(type="string", example="The facility id field is required.")),
     *                 @OA\Property(property="date", type="array", @OA\Items(type="string", example="The date field is required."))
     *             )
     *         )
     *     )
     * )
     * 
     * @param GetAvailableSpotsRequest $request
     * @return JsonResponse
     */
    public function getAvailableSpots(GetAvailableSpotsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $facilityId = (int) $validatedData['facility_id'];
        $date = Carbon::parse($validatedData['date']);
        
        $availableSpots = $this->reservationService->getAvailableSpotsWithTimeSlots($facilityId, $date);
        
        return response()->json([
            'data' => $availableSpots,
        ]);
    }
} 