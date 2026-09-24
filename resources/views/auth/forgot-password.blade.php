<x-layouts.auth title="Lupa kata sandi" heading="Lupa kata sandi?" subheading="Masukkan email akunmu. Kami kirim tautan untuk membuat kata sandi baru.">
    @if (session('status'))
        <div class="mb-6 rounded-xl border border-success-line bg-success-soft p-3.5 text-sm font-medium text-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-input name="email" type="email" label="Email" autocomplete="username" required autofocus />
        <x-button type="submit" size="lg" class="w-full" icon="mail">Kirim tautan</x-button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-500">
        Ingat kata sandimu? <a href="{{ route('login') }}" class="link">Masuk</a>
    </p>
</x-layouts.auth>
