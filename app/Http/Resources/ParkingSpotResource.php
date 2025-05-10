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
            'facility' => $this->when($this->relationLoaded('facility') && $this->facility !== null, function () {
                return new FacilityResource($this->facility);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
