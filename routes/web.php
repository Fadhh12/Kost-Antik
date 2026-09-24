<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Styleguide komponen: hanya di environment local.
if (app()->environment('local')) {
    Route::view('/_styleguide', 'styleguide')->name('styleguide');
}

/*
|--------------------------------------------------------------------------
| Area login
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'account.active'])->group(function () {
    // FR-AUTH-04: arahkan sesuai role.
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('app')->name('app.')->middleware('role:tenant')->group(base_path('routes/tenant.php'));
    Route::prefix('admin')->name('admin.')->middleware('role:owner|manager')->group(base_path('routes/admin.php'));
});

require __DIR__.'/auth.php';
