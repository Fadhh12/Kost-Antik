{{-- FR-AUTH-02: banner status akun penyewa. --}}
@props(['user'])

@if ($user->isPending())
    <div class="border-b border-warning-line bg-warning-soft">
        <div class="mx-auto flex max-w-6xl items-start gap-3 px-4 py-3 sm:px-6">
            <x-lucide-hourglass class="mt-0.5 h-5 w-5 shrink-0 text-warning" stroke-width="1.75" aria-hidden="true" />
            <p class="text-sm text-ink-700">
                <span class="font-semibold text-ink-900">Akun menunggu verifikasi.</span>
                Kamu sudah bisa melihat-lihat kost, tapi pengajuan sewa baru bisa dikirim setelah pemilik memverifikasi akunmu. Biasanya kurang dari 1x24 jam.
            </p>
        </div>
    </div>
@endif
