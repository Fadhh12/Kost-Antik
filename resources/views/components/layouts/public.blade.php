@props(['title' => null, 'description' => null, 'image' => null, 'transparentNav' => false])

@php
    $contact = config('kost.contact');
@endphp

<x-layouts.base :title="$title" :description="$description ?? 'Cari kamar kost yang tersedia di Bekasi dan Cikarang, ajukan sewa online, dan bayar tagihan dengan bukti transfer yang tercatat rapi.'" :image="$image">
    {{-- Navbar --}}
    <header x-data="{ open: false }" class="sticky top-0 z-nav border-b border-kapur-200/80 bg-kapur-50/90 backdrop-blur supports-[backdrop-filter]:bg-kapur-50/75">
        <div class="mx-auto flex h-16 max-w-page items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="Kost Antik, ke beranda">
                <x-application-logo />
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Utama">
                @php
                    $links = [
                        ['label' => 'Cari Kost', 'route' => 'kost.index', 'active' => 'kost.*'],
                        ['label' => 'Cara Sewa', 'href' => route('home').'#cara-sewa'],
                        ['label' => 'Kontak', 'href' => '#kontak'],
                    ];
                @endphp
                @foreach ($links as $link)
                    @if (isset($link['route']) && ! Route::has($link['route']))
                        @continue
                    @endif
                    @php $active = isset($link['active']) && request()->routeIs($link['active']); @endphp
                    <a href="{{ isset($link['route']) ? route($link['route']) : $link['href'] }}"
                        @if ($active) aria-current="page" @endif
                        @class([
                            'rounded-lg px-3 py-2 text-sm font-semibold transition',
                            'text-tegel-800 bg-tegel-50' => $active,
                            'text-ink-700 hover:text-ink-900 hover:bg-kapur-100' => ! $active,
                        ])>{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    <x-button :href="route('dashboard')" variant="secondary" size="sm" icon="layout-dashboard">Dashboard</x-button>
                @else
                    <x-button :href="route('login')" variant="ghost" size="sm">Masuk</x-button>
                    @if (Route::has('register'))
                        <x-button :href="route('register')" size="sm">Daftar</x-button>
                    @endif
                @endauth
            </div>

            <button type="button" x-on:click="open = ! open" class="-mr-2 rounded-lg p-2 text-ink-700 hover:bg-kapur-100 md:hidden"
                :aria-expanded="open" aria-controls="menu-mobile">
                <span class="sr-only">Buka menu</span>
                <x-lucide-menu class="h-6 w-6" stroke-width="1.75" x-show="! open" aria-hidden="true" />
                <x-lucide-x class="h-6 w-6" stroke-width="1.75" x-show="open" x-cloak aria-hidden="true" />
            </button>
        </div>

        {{-- Menu mobile --}}
        <div id="menu-mobile" x-show="open" x-cloak x-transition.opacity class="border-t border-kapur-200 bg-kapur-50 md:hidden">
            <nav class="space-y-1 px-4 py-3" aria-label="Utama (mobile)">
                @if (Route::has('kost.index'))
                    <a href="{{ route('kost.index') }}" class="block rounded-lg px-3 py-2.5 font-semibold text-ink-900 hover:bg-kapur-100">Cari Kost</a>
                @endif
                <a href="{{ route('home') }}#cara-sewa" x-on:click="open = false" class="block rounded-lg px-3 py-2.5 font-semibold text-ink-900 hover:bg-kapur-100">Cara Sewa</a>
                <a href="#kontak" x-on:click="open = false" class="block rounded-lg px-3 py-2.5 font-semibold text-ink-900 hover:bg-kapur-100">Kontak</a>
            </nav>
            <div class="grid grid-cols-2 gap-2 border-t border-kapur-200 px-4 py-3">
                @auth
                    <x-button :href="route('dashboard')" class="col-span-2" icon="layout-dashboard">Dashboard</x-button>
                @else
                    <x-button :href="route('login')" variant="secondary">Masuk</x-button>
                    <x-button :href="route('register')">Daftar</x-button>
                @endauth
            </div>
        </div>
    </header>

    <main id="konten">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer id="kontak" class="relative mt-24 overflow-hidden bg-tegel-900 text-kapur-100">
        <div class="bg-tegel pointer-events-none absolute inset-0 opacity-[0.07]" aria-hidden="true"></div>
        <x-tegel-divider class="relative opacity-70" />
        <div class="relative mx-auto grid max-w-page gap-10 px-4 py-14 sm:px-6 md:grid-cols-12 lg:px-8">
            <div class="md:col-span-5">
                <x-application-logo tone="light" />
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-tegel-100">
                    Kost untuk mahasiswa dan pekerja di Bekasi dan Cikarang. Kamar yang jelas statusnya, tagihan yang tercatat, pengelola yang bisa dihubungi.
                </p>
            </div>

            <div class="md:col-span-4">
                <h2 class="font-display text-base font-semibold text-white">Kantor pengelola</h2>
                <ul class="mt-4 space-y-3 text-sm text-tegel-100">
                    <li class="flex gap-3">
                        <x-lucide-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        <span>{{ $contact['address'] }}</span>
                    </li>
                    <li class="flex gap-3">
                        <x-lucide-clock class="mt-0.5 h-4 w-4 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        <span>{{ $contact['hours'] }}</span>
                    </li>
                    <li class="flex gap-3">
                        <x-lucide-phone class="mt-0.5 h-4 w-4 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="hover:text-white">{{ $contact['phone'] }}</a>
                    </li>
                    <li class="flex gap-3">
                        <x-lucide-mail class="mt-0.5 h-4 w-4 shrink-0 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        <a href="mailto:{{ $contact['email'] }}" class="hover:text-white">{{ $contact['email'] }}</a>
                    </li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="font-display text-base font-semibold text-white">Jelajahi</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-tegel-100">
                    @if (Route::has('kost.index'))
                        <li><a href="{{ route('kost.index') }}" class="hover:text-white">Semua kost</a></li>
                        <li><a href="{{ route('kost.index', ['gender' => 'female']) }}" class="hover:text-white">Kost putri</a></li>
                        <li><a href="{{ route('kost.index', ['gender' => 'male']) }}" class="hover:text-white">Kost putra</a></li>
                    @endif
                    <li><a href="{{ route('login') }}" class="hover:text-white">Masuk penghuni</a></li>
                </ul>
            </div>
        </div>
        <div class="relative border-t border-white/10">
            <p class="mx-auto max-w-page px-4 py-5 text-xs text-tegel-200 sm:px-6 lg:px-8">&copy; {{ now()->year }} Kost Antik. Semua hak dilindungi.</p>
        </div>
    </footer>
</x-layouts.base>
