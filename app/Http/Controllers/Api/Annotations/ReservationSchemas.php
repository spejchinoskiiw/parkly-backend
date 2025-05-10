<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Annotations;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     title="Reservation",
 *     description="Parking spot reservation model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="Reservation ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1, description="User ID who made the reservation"),
 *     @OA\Property(property="parking_spot_id", type="integer", format="int64", example=1, description="Parking spot ID that was reserved"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-15T14:30:00", description="Reservation start time"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-15T16:30:00", nullable=true, description="Reservation end time (null for on-demand reservations)"),
 *     @OA\Property(property="type", type="string", enum={"ondemand", "scheduled"}, example="scheduled", description="Reservation type"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-15T12:00:00Z", description="When the reservation was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-15T12:00:00Z", description="When the reservation was last updated")
 * )
 * 
 * @OA\Schema(
 *     schema="ReservationCollection",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Reservation")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://parkly.com/api/reservations?page=1"),
 *         @OA\Property(property="last", type="string", example="http://parkly.com/api/reservations?page=1"),
 *         @OA\Property(property="prev", type="string", example=null, nullable=true),
 *         @OA\Property(property="next", type="string", example=null, nullable=true)
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=1),
 *         @OA\Property(property="path", type="string", example="http://parkly.com/api/reservations"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=10),
 *         @OA\Property(property="total", type="integer", example=10)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="CreateOnDemandReservationRequest",
 *     type="object",
 *     required={"parking_spot_id", "start_time"},
 *     @OA\Property(property="parking_spot_id", type="integer", example=1, description="ID of the parking spot to reserve"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-15T14:30:00", description="When the reservation starts")
 * )
 * 
 * @OA\Schema(
 *     schema="CreateScheduledReservationRequest",
 *     type="object",
 *     required={"parking_spot_id", "start_time", "end_time"},
 *     @OA\Property(property="parking_spot_id", type="integer", example=1, description="ID of the parking spot to reserve"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2023-05-15T14:30:00", description="When the reservation starts"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2023-05-15T16:30:00", description="When the reservation ends")
 * )
 * 
 * @OA\Schema(
 *     schema="ReservationResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Reservation created successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/Reservation")
 * )
 * 
 * @OA\Schema(
 *     schema="ReservationUnavailableResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Parking spot is not available for this time period or invalid time range")
 * )
 * 
 * @OA\Schema(
 *     schema="UnauthorizedError",
 *     type="object",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Unauthenticated."
 *     )
 * )
 */
class ReservationSchemas
{
} 