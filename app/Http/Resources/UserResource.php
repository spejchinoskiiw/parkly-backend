<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'facility_id' => $this->facility_id,
            'facility' => $this->when($this->relationLoaded('facility') && $this->facility !== null, function () {
                return [
                    'id' => $this->facility->id,
                    'name' => $this->facility->name,
                    'parking_spot_count' => $this->facility->parking_spot_count,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
