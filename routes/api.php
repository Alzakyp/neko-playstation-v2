<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GameController;
use App\Http\Controllers\API\PlaystationController;
use App\Http\Controllers\API\ReservationController;

// Auth Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Public Routes
Route::get('games', [GameController::class, 'index']);
Route::get('games/{game}', [GameController::class, 'show']);

// Protected Routes (Customer)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // PlayStation
    Route::get('playstations', [PlaystationController::class, 'index']);
    Route::get('playstations/available', [PlaystationController::class, 'available']);
    Route::get('playstations/{playstation}', [PlaystationController::class, 'show']);
    Route::get('playstations/{playstation}/schedule', [PlaystationController::class, 'schedule']);

    // // Reservations
    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{id}', [ReservationController::class, 'show']);
    Route::put('reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('reservations/{id}', [ReservationController::class, 'cancel']);
    Route::get('reservations/active', [ReservationController::class, 'active']);
    Route::get('reservations/history', [ReservationController::class, 'history']);
});

// // Reservations
// Route::get('reservations', [ReservationController::class, 'index']);
// Route::post('reservations', [ReservationController::class, 'store']);
// Route::get('reservations/{id}', [ReservationController::class, 'show']);
// Route::put('reservations/{id}', [ReservationController::class, 'update']);
// Route::delete('reservations/{id}', [ReservationController::class, 'cancel']);
// Route::get('reservations/active', [ReservationController::class, 'active']);
// Route::get('reservations/history', [ReservationController::class, 'history']);
