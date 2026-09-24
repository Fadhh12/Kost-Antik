<?php

use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\LeaseController;
use Illuminate\Support\Facades\Route;

/*
| Penyewa: prefix /app, nama app.*, middleware auth + account.active + role:tenant.
*/

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

Route::get('/leases', [LeaseController::class, 'index'])->name('leases.index');
Route::get('/leases/{lease}', [LeaseController::class, 'show'])->name('leases.show');

Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
