<x-layouts.auth title="Masuk" heading="Selamat datang kembali" subheading="Masuk untuk melihat kontrak, tagihan, dan pengajuan sewamu.">
    @if (session('status'))
        <div class="mb-6 rounded-xl border border-success-line bg-success-soft p-3.5 text-sm font-medium text-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-input name="email" type="email" label="Email" autocomplete="username" required autofocus placeholder="nama@email.com" />

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="f-password" class="text-sm font-medium text-ink-700">Kata sandi</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-tegel-700 hover:text-tegel-900">Lupa kata sandi?</a>
                @endif
            </div>
            <x-input name="password" type="password" autocomplete="current-password" required />
        </div>

        <label for="remember_me" class="flex items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
            <span class="text-sm text-ink-700">Ingat saya di perangkat ini</span>
        </label>

        <x-button type="submit" size="lg" class="w-full">Masuk</x-button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="link">Daftar sebagai penyewa</a>
    </p>

    @if (app()->environment('local'))
        <div class="mt-8 rounded-xl border border-dashed border-kapur-300 p-4 text-xs text-ink-500">
            <p class="font-semibold text-ink-700">Akun demo (sandi: password)</p>
            <p class="mt-1 num">owner@kostantik.test · manager@kostantik.test · tenant@kostantik.test</p>
        </div>
    @endif
</x-layouts.auth>
