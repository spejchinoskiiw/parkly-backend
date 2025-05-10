<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'parking_spot_id',
        'start_time',
        'end_time',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'type' => ReservationType::class,
    ];

    /**
     * Get the user who created the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parking spot for this reservation.
     */
    public function parkingSpot(): BelongsTo
    {
        return $this->belongsTo(ParkingSpot::class);
    }
} 