<?php

use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Penyewa: prefix /app, nama app.*, middleware auth + account.active + role:tenant.
*/

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
