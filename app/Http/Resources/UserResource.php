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
            'facility' => $this->whenLoadedAndNotNull('facility', function () {
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
