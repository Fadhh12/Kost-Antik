<x-layouts.auth title="Konfirmasi kata sandi" heading="Konfirmasi kata sandi" subheading="Ini area yang dilindungi. Masukkan kata sandimu untuk melanjutkan.">
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf
        <x-input name="password" type="password" label="Kata sandi" autocomplete="current-password" required autofocus />
        <x-button type="submit" size="lg" class="w-full">Konfirmasi</x-button>
    </form>
</x-layouts.auth>
