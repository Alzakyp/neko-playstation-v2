<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlaystationController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\Playstation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Import Breeze authentication routes (without middleware for now)
// The registration routes will be disabled in auth.php
require __DIR__ . '/auth.php';

// Dashboard with admin middleware
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('dashboard');

// Profile routes (compatible with Breeze) - require auth but not admin
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes with auth and admin middleware
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // PlayStation Availability routes
        Route::get('playstation/availability', [PlaystationController::class, 'availability'])->name('playstation.availability');
        Route::get('playstation/calendar', [PlaystationController::class, 'calendar'])->name('playstation.calendar');
        Route::get('playstation/daily-report', [PlaystationController::class, 'dailyReport'])->name('playstation.daily-report');
        // PlayStation Routes
        Route::resource('playstation', PlaystationController::class);
        Route::patch('playstation/{playstation}/status', [PlaystationController::class, 'updateStatus'])->name('playstation.update-status');



        // Game Routes
        Route::resource('game', GameController::class);

        // Reservation Routes
        Route::resource('reservation', ReservationController::class);
        Route::prefix('reservations')->name('reservation.')->group(function () {
            Route::get('/active', [ReservationController::class, 'active'])->name('active');
            Route::get('/history', [ReservationController::class, 'history'])->name('history');
            Route::post('/{id}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
            Route::post('/{reservation}/update-status', [ReservationController::class, 'updateStatus'])->name('update-status');
        });

        // Payment Routes
        Route::resource('payment', PaymentController::class);
        Route::patch('payment/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payment.update-status');

        // Refund Routes
        Route::resource('refund', RefundController::class);
        Route::patch('refund/{refund}/process', [RefundController::class, 'processRefund'])->name('refund.process');

        // API endpoints for admin
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('available-slots', [PlaystationController::class, 'getAvailableSlots'])->name('available-slots');
            Route::get('calendar-events', [PlaystationController::class, 'getCalendarEvents'])->name('calendar-events');
        });
    });


