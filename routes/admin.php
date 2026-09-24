<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Admin: prefix /admin, nama admin.*, middleware auth + account.active + role:owner|manager.
| Aksi khusus owner dibatasi lewat Policy.
*/

Route::get('/dashboard', DashboardController::class)->name('dashboard');
