<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Facility gates
        Gate::define('viewAny-facility', fn(User $user) => true);
        
        Gate::define('create-facility', fn(User $user) => $user->role === 'admin');
        
        Gate::define('update-facility', function (User $user, Facility $facility) {
            return $user->role === 'admin' || 
                  ($user->role === 'manager' && $facility->manager_id === $user->id);
        });
        
        Gate::define('delete-facility', function (User $user, Facility $facility) {
            return $user->role === 'admin' || 
                  ($user->role === 'manager' && $facility->manager_id === $user->id);
        });

        // Parking spot gates
        Gate::define('viewAny-parking-spot', fn(User $user) => true);
        
        Gate::define('create-parking-spot', function (User $user, Facility $facility) {
            return $user->role === 'admin' || 
                  ($user->role === 'manager' && $facility->manager_id === $user->id);
        });
        
        Gate::define('update-parking-spot', function (User $user, ParkingSpot $parkingSpot) {
            return $user->role === 'admin' || 
                  ($user->role === 'manager' && $parkingSpot->facility->manager_id === $user->id);
        });
        
        Gate::define('delete-parking-spot', function (User $user, ParkingSpot $parkingSpot) {
            return $user->role === 'admin' || 
                  ($user->role === 'manager' && $parkingSpot->facility->manager_id === $user->id);
        });
    }
}
