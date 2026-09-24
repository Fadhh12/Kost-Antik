<x-layouts.auth title="Verifikasi email" heading="Cek emailmu" subheading="Kami sudah mengirim tautan verifikasi. Klik tautan itu untuk mengaktifkan email akunmu.">
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 rounded-xl border border-success-line bg-success-soft p-3.5 text-sm font-medium text-success" role="status">
            Tautan verifikasi baru sudah dikirim ke email yang kamu daftarkan.
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit" icon="mail">Kirim ulang</x-button>
        </form>
        <form method="POST" action="{{ route('logout') }}" data-no-lock>
            @csrf
            <x-button type="submit" variant="ghost">Keluar</x-button>
        </form>
    </div>
</x-layouts.auth>
