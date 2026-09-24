{{-- Layout split untuk login/daftar/lupa sandi (layar P5). --}}
@props(['title' => null, 'heading' => null, 'subheading' => null, 'wide' => false])

<x-layouts.base :title="$title" body-class="bg-white">
    <div class="grid min-h-[100dvh] lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
        {{-- Panel brand --}}
        <aside class="relative hidden overflow-hidden bg-tegel-900 lg:flex lg:flex-col lg:justify-between">
            <div class="bg-tegel pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-40 -right-40 h-[28rem] w-[28rem] rounded-full bg-tegel-700/60 blur-3xl" aria-hidden="true"></div>

            <div class="relative p-10">
                <a href="{{ route('home') }}" aria-label="Kost Antik, ke beranda"><x-application-logo tone="light" /></a>
            </div>

            <div class="relative px-10 pb-12">
                <p class="max-w-md font-display text-4xl font-semibold leading-[1.1] tracking-tight text-white">
                    Kamar yang jelas, tagihan yang <span class="text-kuningan-300">rapi</span>.
                </p>
                <ul class="mt-8 max-w-md space-y-4 text-sm text-tegel-100">
                    <li class="flex gap-3">
                        <x-lucide-door-open class="h-5 w-5 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        Lihat kamar yang benar-benar kosong, lengkap dengan harga dan fasilitasnya.
                    </li>
                    <li class="flex gap-3">
                        <x-lucide-receipt-text class="h-5 w-5 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        Tagihan bulanan muncul otomatis. Unggah bukti transfer, pengelola memverifikasi.
                    </li>
                    <li class="flex gap-3">
                        <x-lucide-shield-check class="h-5 w-5 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        Setiap akun penghuni diverifikasi pemilik sebelum bisa mengajukan sewa.
                    </li>
                </ul>
            </div>
        </aside>

        {{-- Form --}}
        <main id="konten" class="flex flex-col px-4 py-8 sm:px-8">
            <div class="flex items-center justify-between lg:justify-end">
                <a href="{{ route('home') }}" class="lg:hidden" aria-label="Kost Antik, ke beranda"><x-application-logo /></a>
                <a href="{{ Route::has('kost.index') ? route('kost.index') : route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 transition hover:text-tegel-700">
                    <x-lucide-arrow-left class="h-4 w-4" stroke-width="2" aria-hidden="true" />
                    Lihat kost
                </a>
            </div>

            <div class="flex flex-1 items-center justify-center py-10">
                <div @class(['w-full', 'max-w-md' => ! $wide, 'max-w-xl' => $wide])>
                    @if ($heading)
                        <h1 class="font-display text-3xl font-semibold tracking-tight text-ink-900">{{ $heading }}</h1>
                    @endif
                    @if ($subheading)
                        <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $subheading }}</p>
                    @endif
                    <div class="mt-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.base>
