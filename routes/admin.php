<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\InstanceController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyImageController;
use App\Http\Controllers\Admin\RoomController;
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

// Khusus owner
Route::middleware('role:owner')->group(function () {
    Route::resource('facilities', FacilityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('instances', InstanceController::class)->only(['index', 'store', 'update', 'destroy']);
});
