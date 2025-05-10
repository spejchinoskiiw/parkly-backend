<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Facility extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'parking_spot_count',
        'manager_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parking_spot_count' => 'integer',
    ];

    /**
     * Get the parking spots that belong to the facility.
     */
    public function parkingSpots(): HasMany
    {
        return $this->hasMany(ParkingSpot::class);
    }

    /**
     * Get the manager that owns the facility.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    /**
     * Get the users who have this facility as their home facility.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
