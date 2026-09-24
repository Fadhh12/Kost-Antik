<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\InstanceController;
use Illuminate\Support\Facades\Route;

/*
| Admin: prefix /admin, nama admin.*, middleware auth + account.active + role:owner|manager.
| Aksi per data dibatasi Policy; menu khusus owner memakai role:owner.
*/

Route::get('/dashboard', DashboardController::class)->name('dashboard');

// Khusus owner
Route::middleware('role:owner')->group(function () {
    Route::resource('facilities', FacilityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('instances', InstanceController::class)->only(['index', 'store', 'update', 'destroy']);
});
