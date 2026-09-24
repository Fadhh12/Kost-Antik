<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\InstanceController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyImageController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
| Admin: prefix /admin, nama admin.*, middleware auth + account.active + role:owner|manager.
| Aksi per data dibatasi Policy; menu khusus owner memakai role:owner.
*/

Route::get('/dashboard', DashboardController::class)->name('dashboard');

// Gedung, foto, kamar
Route::resource('properties', PropertyController::class);
Route::post('properties/{property}/images', [PropertyImageController::class, 'store'])->name('properties.images.store');
Route::patch('property-images/{image}/cover', [PropertyImageController::class, 'cover'])->name('property-images.cover');
Route::delete('property-images/{image}', [PropertyImageController::class, 'destroy'])->name('property-images.destroy');
Route::resource('properties.rooms', RoomController::class)->only(['store', 'update', 'destroy'])->scoped();

// Booking
Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::patch('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
Route::patch('bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

// Pengguna
Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

// Khusus owner
Route::middleware('role:owner')->group(function () {
    Route::post('users/managers', [UserController::class, 'storeManager'])->name('users.managers.store');
    Route::patch('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::patch('users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

    Route::resource('facilities', FacilityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('instances', InstanceController::class)->only(['index', 'store', 'update', 'destroy']);
});
