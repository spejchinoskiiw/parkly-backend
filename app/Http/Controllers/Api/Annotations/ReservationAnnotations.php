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
}