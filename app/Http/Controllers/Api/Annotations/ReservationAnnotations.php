<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Annotations;

/**
 * @OA\Tag(
 *     name="Reservations",
 *     description="API Endpoints for managing parking spot reservations"
 * )
 */
class ReservationAnnotations
{
    /**
     * @OA\Post(
     *     path="/api/reservations/ondemand",
     *     summary="Create an on-demand reservation",
     *     description="Create a new on-demand reservation for a parking spot. On-demand reservations have a start time but no end time.",
     *     operationId="createOnDemandReservation",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reservation details",
     *         @OA\JsonContent(ref="#/components/schemas/CreateOnDemandReservationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReservationResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or parking spot not available",
     *         @OA\JsonContent(ref="#/components/schemas/ReservationUnavailableResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking spot not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Parking spot not found")
     *         )
     *     )
     * )
     */
    public function createOnDemand()
    {
        // This is a dummy method for Swagger annotations only
    }

    /**
     * @OA\Post(
     *     path="/api/reservations/scheduled",
     *     summary="Create a scheduled reservation",
     *     description="Create a new scheduled reservation for a parking spot with specific start and end times. Scheduled reservations must have both start and end times.",
     *     operationId="createScheduledReservation",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Scheduled reservation details",
     *         @OA\JsonContent(ref="#/components/schemas/CreateScheduledReservationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReservationResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error, invalid time range, or parking spot not available",
     *         @OA\JsonContent(ref="#/components/schemas/ReservationUnavailableResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking spot not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Parking spot not found")
     *         )
     *     )
     * )
     */
    public function createScheduled()
    {
        // This is a dummy method for Swagger annotations only
    }
    
    /**
     * @OA\Get(
     *     path="/api/reservations/reservationsForDate",
     *     summary="Get user reservations for a specific date",
     *     description="Returns all reservations for the authenticated user on a given date",
     *     operationId="getUserReservationsForDate",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date to get reservations for (format: Y-m-d)",
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
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="parking_spot_id", type="integer", example=1),
     *                     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-20 09:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-20 17:00:00"),
     *                     @OA\Property(property="type", type="string", example="scheduled"),
     *                     @OA\Property(
     *                         property="parking_spot",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="facility_id", type="integer", example=1),
     *                         @OA\Property(property="spot_number", type="integer", example=1),
     *                         @OA\Property(
     *                             property="facility",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Skopje Office")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
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
     *                 @OA\Property(property="date", type="array", @OA\Items(type="string", example="The date field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function getUserReservationsForDate()
    {
        // This is a dummy method for Swagger annotations only
    }

    /**
     * @OA\Get(
     *     path="/api/available-spots",
     *     summary="Get available parking spots with time slots",
     *     description="Returns a list of available parking spots with their available time slots for a specific date and facility",
     *     operationId="getAvailableSpots",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
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
     *                         "parking_spot_id": 5,
     *                         "time_slots": {
     *                             {"start": "2023-05-20 08:00:00", "end": "2023-05-20 13:00:00"},
     *                             {"start": "2023-05-20 15:00:00", "end": "2023-05-20 17:00:00"}
     *                         },
     *                         "all_day": false
     *                     },
     *                     "2": {
     *                         "parking_spot_id": 8,
     *                         "time_slots": {
     *                             {"start": "2023-05-20 08:00:00", "end": "2023-05-20 17:00:00"}
     *                         },
     *                         "all_day": true
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
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
     */
    public function getAvailableSpots()
    {
        // This is a dummy method for Swagger annotations only
    }

    /**
     * @OA\Post(
     *     path="/api/reservations/checkout",
     *     summary="Checkout from a parking spot",
     *     description="End an active reservation for the authenticated user and a specific parking spot",
     *     operationId="checkoutReservation",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Checkout details",
     *         @OA\JsonContent(
     *             required={"parking_spot_id"},
     *             @OA\Property(property="parking_spot_id", type="integer", example=1, description="ID of the parking spot to checkout from")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation checked out successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Reservation checked out successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="parking_spot_id", type="integer", example=1),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-20 09:00:00"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-20 12:30:45"),
     *                 @OA\Property(property="type", type="string", example="on_demand")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active reservation found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No active reservation found for this parking spot")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
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
     *                 @OA\Property(property="parking_spot_id", type="array", @OA\Items(type="string", example="The parking spot id field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function checkout()
    {
        // This is a dummy method for Swagger annotations only
    }

    /**
     * @OA\Patch(
     *     path="/api/reservations/{reservation}",
     *     summary="Update a reservation",
     *     description="Update an existing reservation with new start or end times. Authorization rules apply: admins can update any reservation, managers can update reservations in their facility, users can only update their own reservations.",
     *     operationId="updateReservation",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="reservation",
     *         in="path",
     *         description="ID of the reservation to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reservation update data",
     *         @OA\JsonContent(
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-20 14:00:00", description="New start time for the reservation (must be a future time)"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-20 16:00:00", description="New end time for the reservation (must be after start time)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Reservation updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="parking_spot_id", type="integer", example=1),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-20 14:00:00"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-20 16:00:00"),
     *                 @OA\Property(property="type", type="string", example="scheduled")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update this reservation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have permission to update this reservation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or reservation conflict",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unable to update reservation. The requested time slot might not be available or the time range is invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function update()
    {
        // This is a dummy method for Swagger annotations only
    }

    /**
     * @OA\Delete(
     *     path="/api/reservations/{reservation}",
     *     summary="Delete a reservation",
     *     description="Delete an existing reservation. Authorization rules apply: admins can delete any reservation, managers can delete reservations in their facility, users can only delete their own reservations.",
     *     operationId="deleteReservation",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="reservation",
     *         in="path",
     *         description="ID of the reservation to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Reservation deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete this reservation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have permission to delete this reservation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function destroy()
    {
        // This is a dummy method for Swagger annotations only
    }

    /**
     * @OA\Get(
     *     path="/api/reservations/active",
     *     summary="Get active and pending reservations",
     *     description="Returns all active and pending reservations for the authenticated user. Active reservations are those where the current time is between start and end time or on-demand reservations that have started but not ended. Pending reservations are those with a start time in the future.",
     *     operationId="getActiveReservations",
     *     tags={"Reservations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="parking_spot_id", type="integer", example=1),
     *                     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-20 09:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-20 17:00:00"),
     *                     @OA\Property(property="type", type="string", example="scheduled"),
     *                     @OA\Property(
     *                         property="parking_spot",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="facility_id", type="integer", example=1),
     *                         @OA\Property(property="spot_number", type="integer", example=1),
     *                         @OA\Property(
     *                             property="facility",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Skopje Office")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function getActiveReservations()
    {
        // This is a dummy method for Swagger annotations only
    }
}