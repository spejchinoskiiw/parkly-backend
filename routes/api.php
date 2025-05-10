<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\ParkingSpotController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ReservationController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-pin', [AuthController::class, 'verifyPin']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User profile routes
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    
    Route::get('/user', function () {
        return auth()->user();
    });

    // Facilities routes
    Route::apiResource('facilities', FacilityController::class);

    // Parking spots routes
    Route::apiResource('parking-spots', ParkingSpotController::class);
    
    // Reservation routes
    Route::post('/reservations/ondemand', [ReservationController::class, 'createOnDemand']);
    Route::post('/reservations/scheduled', [ReservationController::class, 'createScheduled']);
    Route::get('/reservations/reservationsForDate', [ReservationController::class, 'getUserReservationsForDate']);
    Route::get('/reservations/active', [ReservationController::class, 'getActiveReservations']);
    Route::post('/reservations/checkout', [ReservationController::class, 'checkout']);
    Route::patch('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
    Route::get('/available-spots', [ReservationController::class, 'getAvailableSpots']);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-email', [EmailVerificationController::class, 'verify']);
 