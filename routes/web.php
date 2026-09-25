<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PropertyCatalogController;
use App\Http\Controllers\Public\PropertySubmissionController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/kost', [PropertyCatalogController::class, 'index'])->name('kost.index');
Route::get('/kost/{property:slug}', [PropertyCatalogController::class, 'show'])->name('kost.show');

// F-04: tamu yang klik "Ajukan sewa" diarahkan ke login/daftar, lalu kembali ke kamar yang dipilih.
Route::get('/kost/{property:slug}/ajukan', [PropertyCatalogController::class, 'apply'])
    ->middleware('auth')
    ->name('kost.apply');

// F-XX: pemilik kost lain mendaftarkan gedungnya untuk ditinjau admin.
Route::get('/daftar-kost', [PropertySubmissionController::class, 'create'])->name('kost.submissions.create');
Route::post('/daftar-kost', [PropertySubmissionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('kost.submissions.store');

// Styleguide & login cepat: hanya di environment local dengan APP_DEBUG=true.
if (app()->environment('local') && config('app.debug')) {
    Route::view('/_styleguide', 'styleguide')->name('styleguide');

    // Masuk cepat sebagai akun demo untuk pengecekan visual (screenshot).
    Route::get('/_login-as/{email}', function (string $email) {
        auth()->login(User::where('email', $email)->firstOrFail());

        return redirect(request('to', '/dashboard'));
    })->name('dev.login-as');
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

    // FR-PAY-06: bukti bayar privat
    Route::get('/payments/{payment}/proof', PaymentProofController::class)->name('payments.proof');

    Route::prefix('app')->name('app.')->middleware('role:tenant')->group(base_path('routes/tenant.php'));
    Route::prefix('admin')->name('admin.')->middleware('role:owner|manager')->group(base_path('routes/admin.php'));
});

require __DIR__.'/auth.php';
