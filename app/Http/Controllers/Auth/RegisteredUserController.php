<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Instance;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'instances' => Instance::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(RegisterRequest $request, UserService $users): RedirectResponse
    {
        $user = $users->registerTenant($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Akun berhasil dibuat. Pemilik kost akan memverifikasi akunmu.');
    }
}
