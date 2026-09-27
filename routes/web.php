<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingApiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OwnerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Customer & Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/track/{reference}', [HomeController::class, 'trackBooking'])->name('booking.track');
Route::get('/lookup', [HomeController::class, 'lookup'])->name('booking.lookup');
Route::post('/booking/manual-receipt', [BookingApiController::class, 'submitManualReceipt'])->name('booking.manual_receipt');
Route::get('/xendit-checkout/{reference}', [BookingApiController::class, 'simulatedCheckout'])->name('xendit.simulated.checkout');
Route::get('/paymongo-checkout/{reference}', [BookingApiController::class, 'paymongoCheckout'])->name('paymongo.simulated.checkout');
Route::post('/simulate-payment/{reference}', [BookingApiController::class, 'simulatePayment'])->name('booking.simulate_payment');

/*
|--------------------------------------------------------------------------
| Public API Endpoints (Real-time slots, holds, status)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {
    Route::get('/availability', [BookingApiController::class, 'availability'])->name('api.availability');
    Route::post('/hold-slots', [BookingApiController::class, 'hold'])->name('api.hold');
    Route::get('/booking-status/{reference}', [BookingApiController::class, 'status'])->name('api.booking_status');
    Route::post('/cancel-hold/{reference}', [BookingApiController::class, 'cancelHold'])->name('api.cancel_hold');
    Route::post('/xendit/webhook', [BookingApiController::class, 'xenditWebhook'])->name('api.xendit.webhook');
    Route::post('/paymongo/webhook', [BookingApiController::class, 'paymongoWebhook'])->name('api.paymongo.webhook');
    Route::post('/paymongo/dynamic-qr/{reference}', [BookingApiController::class, 'generatePayMongoQr'])->name('api.paymongo.dynamic_qr');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Owner & Admin Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:court_owner,admin'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [OwnerController::class, 'dashboard'])->name('dashboard');

    // Manual Receipt Approvals Queue
    Route::get('/approvals', [OwnerController::class, 'approvals'])->name('approvals');
    Route::post('/approvals/{id}/approve', [OwnerController::class, 'approveBooking'])->name('approve');
    Route::post('/approvals/{id}/reject', [OwnerController::class, 'rejectBooking'])->name('reject');

    // Courts Management (Pricing per hour, max players, photo, add/edit)
    Route::get('/courts', [OwnerController::class, 'courts'])->name('courts.index');
    Route::post('/courts', [OwnerController::class, 'storeCourt'])->name('courts.store');
    Route::put('/courts/{id}', [OwnerController::class, 'updateCourt'])->name('courts.update');
    Route::delete('/courts/{id}', [OwnerController::class, 'destroyCourt'])->name('courts.destroy');
    Route::post('/courts/{id}/restore', [OwnerController::class, 'restoreCourt'])->name('courts.restore');

    // Facility Photos Management for Website Main Page
    Route::get('/photos', [OwnerController::class, 'photos'])->name('photos.index');
    Route::post('/photos', [OwnerController::class, 'storePhoto'])->name('photos.store');
    Route::delete('/photos/{id}', [OwnerController::class, 'destroyPhoto'])->name('photos.destroy');

    // Venue & Payment Gateway Settings (Xendit vs Manual Receipt)
    Route::get('/settings', [OwnerController::class, 'settings'])->name('settings');
    Route::put('/settings', [OwnerController::class, 'updateSettings'])->name('settings.update');

    // All Bookings Ledger
    Route::get('/bookings', [OwnerController::class, 'allBookings'])->name('bookings.index');
});
