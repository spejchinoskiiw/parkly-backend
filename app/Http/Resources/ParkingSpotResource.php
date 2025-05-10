<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ParkingSpotResource extends JsonResource
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
            'facility_id' => $this->facility_id,
            'spot_number' => $this->spot_number,
            'facility' => $this->whenLoadedAndNotNull('facility', function () {
                return new FacilityResource($this->facility);
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
