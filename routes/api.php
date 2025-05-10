<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-pin', [AuthController::class, 'verifyPin']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function () {
        return auth()->user();
    });
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-email', [EmailVerificationController::class, 'verify']);
