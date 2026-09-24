<?php

use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Penyewa: prefix /app, nama app.*, middleware auth + account.active + role:tenant.
*/

Route::get('/dashboard', DashboardController::class)->name('dashboard');
