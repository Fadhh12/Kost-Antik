<x-layouts.auth title="Daftar" heading="Daftar sebagai penyewa" subheading="Setelah mendaftar, pemilik kost akan memverifikasi akunmu sebelum kamu bisa mengajukan sewa." :wide="true">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div class="flex gap-3 rounded-xl border border-tegel-100 bg-tegel-50 p-3.5 text-sm text-tegel-800">
            <x-lucide-shield-check class="h-5 w-5 shrink-0" stroke-width="1.75" aria-hidden="true" />
            <p>Gunakan nama dan nomor HP yang aktif. Data ini dipakai pengelola untuk menghubungimu soal kamar dan tagihan.</p>
        </div>

        <x-input name="name" label="Nama lengkap" autocomplete="name" required autofocus placeholder="Sesuai KTP" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-input name="email" type="email" label="Email" autocomplete="email" required placeholder="nama@email.com" />
            <x-input name="phone" type="tel" label="Nomor HP (WhatsApp)" autocomplete="tel" required placeholder="081234567890" />
        </div>

        <fieldset>
            <legend class="label">Jenis kelamin <span class="text-danger" aria-hidden="true">*</span></legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (\App\Enums\Gender::cases() as $gender)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-kapur-300 bg-white px-3.5 py-2.5 text-sm font-medium shadow-tile transition has-[:checked]:border-tegel-600 has-[:checked]:bg-tegel-50 has-[:checked]:text-tegel-800">
                        <input type="radio" name="gender" value="{{ $gender->value }}" @checked(old('gender') === $gender->value) required
                            class="h-4 w-4 border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                        {{ $gender->label() }}
                    </label>
                @endforeach
            </div>
            <p class="mt-1.5 text-xs text-ink-500">Menentukan kost putra/putri yang bisa kamu sewa.</p>
            <x-input-error for="gender" />
        </fieldset>

        <x-select name="instance_id" label="Kampus atau kantor" :options="$instances" placeholder="Pilih (opsional)" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-input name="password" type="password" label="Kata sandi" autocomplete="new-password" required hint="Minimal 8 karakter, berisi huruf dan angka." />
            <x-input name="password_confirmation" type="password" label="Ulangi kata sandi" autocomplete="new-password" required />
        </div>

        <x-button type="submit" size="lg" class="w-full">Buat akun</x-button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="link">Masuk</a>
    </p>
</x-layouts.auth>
