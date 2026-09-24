<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        // SRS 2.2: minimal 8 karakter, berisi huruf & angka.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Tangkap N+1 saat pengembangan (NFR performa).
        Model::preventLazyLoading($this->app->environment('local'));

        // Owner boleh semua aksi otorisasi. Aturan bisnis (BR-xx) tetap dijaga di Service.
        Gate::before(fn (User $user) => $user->isOwner() ? true : null);
    }
}
