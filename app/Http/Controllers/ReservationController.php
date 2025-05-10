<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\CreateOnDemandReservationRequest;
use App\Http\Requests\CreateScheduledReservationRequest;
use App\Http\Requests\GetAvailableSpotsRequest;
use App\Http\Requests\GetUserReservationsForDateRequest;
use App\Http\Requests\CheckoutReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

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
        
        // Role-based authorization check for managers
        if ($request->user()->role === UserRole::MANAGER->value) {
            if ($parkingSpot->facility_id !== $request->user()->facility_id) {
                return response()->json([
                    'message' => 'You do not have permission to create reservations for this facility',
                ], 403);
            }
        }
        
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
            'data' => new ReservationResource($reservation),
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
        
        // Role-based authorization check for managers
        if ($request->user()->role === UserRole::MANAGER->value) {
            if ($parkingSpot->facility_id !== $request->user()->facility_id) {
                return response()->json([
                    'message' => 'You do not have permission to create reservations for this facility',
                ], 403);
            }
        }
        
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
            'data' => new ReservationResource($reservation),
        ], 201);
    }

    /**
     * Get available parking spots with time slots for a specific date and facility.
     * 
     * This endpoint is documented in:
     * @see \App\Http\Controllers\Api\Annotations\ReservationAnnotations::getAvailableSpots()
     * 
     * @param GetAvailableSpotsRequest $request
     * @return JsonResponse
     */
    public function getAvailableSpots(GetAvailableSpotsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $facilityId = (int) $validatedData['facility_id'];
        
        // Role-based authorization check for managers
        if ($request->user()->role === UserRole::MANAGER->value) {
            if ($facilityId !== $request->user()->facility_id) {
                return response()->json([
                    'message' => 'You do not have permission to view spots for this facility',
                ], 403);
            }
        }
        
        $date = Carbon::parse($validatedData['date']);
        
        $availableSpots = $this->reservationService->getAvailableSpotsWithTimeSlots($facilityId, $date);
        
        return response()->json([
            'data' => $availableSpots,
        ]);
    }

    /**
     * Get all reservations for the authenticated user for a specific date.
     * 
     * This endpoint is documented in:
     * @see \App\Http\Controllers\Api\Annotations\ReservationAnnotations::getUserReservationsForDate()
     * 
     * @param GetUserReservationsForDateRequest $request
     * @return JsonResponse
     */
    public function getUserReservationsForDate(GetUserReservationsForDateRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $date = Carbon::parse($validatedData['date']);
        
        // For admins, show all reservations
        if ($request->user()->role === UserRole::ADMIN->value) {
            $reservations = $this->reservationService->getAllReservationsForDate($date);
        } 
        // For managers, show reservations in their facility
        else if ($request->user()->role === UserRole::MANAGER->value) {
            $reservations = $this->reservationService->getFacilityReservationsForDate(
                $request->user()->facility_id,
                $date
            );
        } 
        // For regular users, show only their reservations
        else {
            $reservations = $this->reservationService->getUserReservationsForDate(
                $request->user(),
                $date
            );
        }
        
        return response()->json([
            'data' => ReservationResource::collection($reservations),
        ]);
    }

    /**
     * Checkout (end) an active reservation.
     * 
     * This endpoint is documented in:
     * @see \App\Http\Controllers\Api\Annotations\ReservationAnnotations::checkout()
     * 
     * @param CheckoutReservationRequest $request
     * @return JsonResponse
     */
    public function checkout(CheckoutReservationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $parkingSpotId = (int) $validatedData['parking_spot_id'];
        
        // First check if there is an active reservation for this spot
        $parkingSpot = ParkingSpot::findOrFail($parkingSpotId);
        $activeReservation = $this->reservationService->getActiveReservationForSpot($parkingSpotId);
        
        if (!$activeReservation) {
            return response()->json([
                'message' => 'No active reservation found for this parking spot',
            ], 404);
        }
        
        // Role-based authorization check
        if ($request->user()->role === UserRole::ADMIN->value) {
            // Admins can checkout any reservation
        } else if ($request->user()->role === UserRole::MANAGER->value) {
            // Managers can only checkout reservations in their facility
            if ($parkingSpot->facility_id !== $request->user()->facility_id) {
                return response()->json([
                    'message' => 'You do not have permission to checkout reservations for this facility',
                ], 403);
            }
        } else {
            // Regular users can only checkout their own reservations
            if ($activeReservation->user_id !== $request->user()->id) {
                // We return 404 for regular users trying to access other users' reservations
                // This maintains the abstraction that the reservation doesn't exist for them
                return response()->json([
                    'message' => 'No active reservation found for this parking spot',
                ], 404);
            }
        }
        
        // We need to use checkoutReservationBySpot instead of checkoutReservation for managers and admins
        // since they might be checking out other users' reservations
        if ($request->user()->role === UserRole::USER->value) {
            $reservation = $this->reservationService->checkoutReservation(
                $request->user(),
                $parkingSpotId
            );
        } else {
            $reservation = $this->reservationService->checkoutReservationBySpot($parkingSpotId);
        }
        
        if (!$reservation) {
            return response()->json([
                'message' => 'No active reservation found for this parking spot',
            ], 404);
        }
        
        return response()->json([
            'message' => 'Reservation checked out successfully',
            'data' => new ReservationResource($reservation),
        ]);
    }

    /**
     * Update an existing reservation.
     * 
     * @param UpdateReservationRequest $request
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        // Check if the user has permission to update this reservation
        if (!$request->user()->can('update', $reservation)) {
            return response()->json([
                'message' => 'You do not have permission to update this reservation',
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        // Check if we have data to update
        if (empty($validatedData)) {
            return response()->json([
                'message' => 'No data provided for update',
            ], 422);
        }
        
        // Update the reservation using the service
        $updatedReservation = $this->reservationService->updateReservation(
            $reservation,
            $validatedData
        );
        
        if (!$updatedReservation) {
            return response()->json([
                'message' => 'Unable to update reservation. The requested time slot might not be available or the time range is invalid.',
            ], 422);
        }
        
        return response()->json([
            'message' => 'Reservation updated successfully',
            'data' => new ReservationResource($updatedReservation),
        ]);
    }

    /**
     * Delete a reservation.
     * 
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        // Check if the user has permission to delete this reservation using the ReservationPolicy
        if (!request()->user()->can('delete', $reservation)) {
            return response()->json([
                'message' => 'You do not have permission to delete this reservation',
            ], 403);
        }
        
        // Delete the reservation
        $reservation->delete();
        
        return response()->json([
            'message' => 'Reservation deleted successfully',
        ]);
    }

    /**
     * Get all active and pending reservations for the authenticated user.
     * 
     * @return JsonResponse
     */
    public function getActiveReservations(): JsonResponse
    {
        $reservations = $this->reservationService->getUserActiveReservations(
            auth()->user()
        );
        
        return response()->json([
            'data' => ReservationResource::collection($reservations),
        ]);
    }
} 