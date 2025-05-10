<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'parking_spot_id' => $this->parking_spot_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'type' => $this->type,
            'user' => $this->when($this->relationLoaded('user') && $this->user !== null, function () {
                return new UserResource($this->user);
            }),
            'parkingSpot' => $this->when($this->relationLoaded('parkingSpot') && $this->parkingSpot !== null, function () {
                return new ParkingSpotResource($this->parkingSpot);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 