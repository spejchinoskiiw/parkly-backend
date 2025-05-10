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
}