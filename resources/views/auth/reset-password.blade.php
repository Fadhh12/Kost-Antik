<x-layouts.auth title="Atur ulang kata sandi" heading="Buat kata sandi baru" subheading="Gunakan minimal 8 karakter yang berisi huruf dan angka.">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-input name="email" type="email" label="Email" :value="$request->email" autocomplete="username" required />
        <x-input name="password" type="password" label="Kata sandi baru" autocomplete="new-password" required autofocus />
        <x-input name="password_confirmation" type="password" label="Ulangi kata sandi" autocomplete="new-password" required />

        <x-button type="submit" size="lg" class="w-full">Simpan kata sandi</x-button>
    </form>
</x-layouts.auth>
