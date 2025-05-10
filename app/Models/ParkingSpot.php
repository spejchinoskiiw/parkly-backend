<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ParkingSpot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facility_id',
        'spot_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'spot_number' => 'integer',
    ];

    /**
     * Get the facility that owns the parking spot.
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
    
    /**
     * Get the reservations for this parking spot.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
