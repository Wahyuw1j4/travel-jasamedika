<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\MyTicketController;

// Auth routes
Route::get('/login', [UsersController::class, 'showLogin'])->name('login');
Route::get('/register', [UsersController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/login', [UsersController::class, 'login'])->middleware('guest');
Route::post('/logout', [UsersController::class, 'logout'])->middleware('auth')->name('logout');



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal-travel', [TravelController::class, 'page'])->name('jadwal.travel');
    Route::get('/jadwal-travel/create', [TravelController::class, 'createPage'])->name('jadwal.travel.create');
    Route::post('/jadwal-travel', [TravelController::class, 'store'])->name('jadwal.travel.store');
    Route::get('/jadwal-travel/{travel}/edit', [TravelController::class, 'editPage'])->name('jadwal.travel.edit');
    Route::get('/jadwal-travel/{travel}', [TravelController::class, 'showPage'])->name('jadwal.travel.show');
    Route::get('/reports', [PaymentController::class, 'reports'])->name('reports');
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    // My tickets page - shows bookings for the authenticated user (passenger only)
    Route::get('/my-ticket', [MyTicketController::class, 'index'])->name('my-ticket')->middleware('role:passenger');
    // Ticket routes: view HTML and download PDF
    Route::get('/tickets/{id}', [\App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{id}/pdf', [\App\Http\Controllers\TicketController::class, 'downloadPdf'])->name('tickets.pdf');
});
