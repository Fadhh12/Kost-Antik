{{-- Layout area penyewa (/app). Navigasi atas di desktop, bottom bar di mobile. --}}
@props(['title' => null, 'heading' => null, 'subheading' => null])

@php
    $nav = [
        ['label' => 'Beranda', 'route' => 'app.dashboard', 'match' => 'app.dashboard', 'icon' => 'house'],
        ['label' => 'Booking', 'route' => 'app.bookings.index', 'match' => 'app.bookings.*', 'icon' => 'calendar-check'],
        ['label' => 'Tagihan', 'route' => 'app.invoices.index', 'match' => 'app.invoices.*', 'icon' => 'receipt-text'],
        ['label' => 'Kontrak', 'route' => 'app.leases.index', 'match' => 'app.leases.*', 'icon' => 'file-signature'],
    ];
    $nav = array_values(array_filter($nav, fn ($i) => Route::has($i['route'])));
@endphp

<x-layouts.base :title="$title ?? $heading">
    <header class="sticky top-0 z-nav border-b border-kapur-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" aria-label="Kost Antik, ke dashboard"><x-application-logo /></a>
                <nav class="hidden items-center gap-1 md:flex" aria-label="Menu penyewa">
                    @foreach ($nav as $item)
                        @php $active = request()->routeIs($item['match']); @endphp
                        <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                            @class([
                                'rounded-lg px-3 py-2 text-sm font-semibold transition',
                                'bg-tegel-50 text-tegel-800' => $active,
                                'text-ink-500 hover:bg-kapur-100 hover:text-ink-900' => ! $active,
                            ])>{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="flex items-center gap-2">
                @if (Route::has('kost.index'))
                    <x-button :href="route('kost.index')" variant="ghost" size="sm" icon="search" class="hidden sm:inline-flex">Cari Kost</x-button>
                @endif
                <x-user-menu />
            </div>
        </div>
    </header>

    @isset($banner)
        {{ $banner }}
    @endisset

    <main id="konten" class="mx-auto max-w-6xl px-4 pb-28 pt-8 sm:px-6 md:pb-16">
        @if ($heading)
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $heading }}</h1>
                    @if ($subheading)
                        <p class="mt-1.5 text-sm text-ink-500">{{ $subheading }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="flex flex-wrap gap-2">{{ $actions }}</div>
                @endisset
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Bottom bar mobile --}}
    @if (count($nav))
        <nav class="fixed inset-x-0 bottom-0 z-nav border-t border-kapur-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden" aria-label="Menu penyewa (mobile)">
            <div class="grid" style="grid-template-columns: repeat({{ count($nav) }}, minmax(0, 1fr))">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                        @class([
                            'flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold transition',
                            'text-tegel-700' => $active,
                            'text-ink-500' => ! $active,
                        ])>
                        <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-5 w-5" :stroke-width="$active ? 2.25 : 1.75" aria-hidden="true" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endif
</x-layouts.base>
