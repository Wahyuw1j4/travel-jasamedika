<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\TravelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UsersController;

// Public API auth
Route::post('/login', [UsersController::class, 'login']);

// Protected API routes (require Sanctum auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('travels', TravelController::class);
    Route::apiResource('bookings', BookingController::class);
    Route::apiResource('payments', PaymentController::class);

    // Get bookings for a specific travel. Admins get all bookings; passengers get only their bookings.
    Route::get('/travels/{travelId}/bookings', [BookingController::class, 'getBookingByTravelId']);
    // Get details for a specific booking under a travel
    Route::get('/travels/{travelId}/bookings/{bookingId}/booking-details', [BookingController::class, 'getBookingDetails']);
});
