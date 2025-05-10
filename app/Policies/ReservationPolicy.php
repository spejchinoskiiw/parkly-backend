<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view reservations list (specific permissions applied in controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        // Admins can view any reservation
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Managers can view reservations for their facility
        if ($user->role === UserRole::MANAGER->value) {
            return $user->facility_id === $reservation->parkingSpot->facility_id;
        }

        // Regular users can only view their own reservations
        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create reservations
        // Specific parking spot availability/authorization handled in the controller/service
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Admins can update any reservation
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Managers can update reservations for their facility
        if ($user->role === UserRole::MANAGER->value) {
            return $user->facility_id === $reservation->parkingSpot->facility_id;
        }

        // Regular users can only update their own reservations
        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Admins can delete any reservation
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Managers can delete reservations for their facility
        if ($user->role === UserRole::MANAGER->value) {
            return $user->facility_id === $reservation->parkingSpot->facility_id;
        }

        // Regular users can only delete their own reservations
        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can checkout a reservation.
     */
    public function checkout(User $user, Reservation $reservation): bool
    {
        // Admins can checkout any reservation
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Managers can checkout reservations for their facility
        if ($user->role === UserRole::MANAGER->value) {
            return $user->facility_id === $reservation->parkingSpot->facility_id;
        }

        // Regular users can only checkout their own reservations
        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        // Admins can restore any reservation
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        // Managers can restore reservations for their facility
        if ($user->role === UserRole::MANAGER->value) {
            return $user->facility_id === $reservation->parkingSpot->facility_id;
        }

        // Regular users cannot restore reservations
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        // Only admins can permanently delete reservations
        return $user->role === UserRole::ADMIN->value;
    }
}
