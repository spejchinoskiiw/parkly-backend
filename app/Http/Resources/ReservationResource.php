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
            'user' => $this->whenLoadedAndNotNull('user', function () {
                return new UserResource($this->user);
            }),
            'parkingSpot' => $this->whenLoadedAndNotNull('parkingSpot', function () {
                return new ParkingSpotResource($this->parkingSpot);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    /**
     * Safely include a loaded relationship in the resource, handling null relations.
     *
     * @param string $relation The name of the relation
     * @param callable $value The value to include when the relation is loaded and not null
     * @return mixed|null The value or null when the relation isn't loaded or is null
     */
    protected function whenLoadedAndNotNull(string $relation, callable $value): mixed
    {
        return $this->when(
            $this->relationLoaded($relation) && $this->{$relation} !== null,
            $value
        );
    }
} 