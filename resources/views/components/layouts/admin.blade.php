{{-- Layout admin (/admin): sidebar di desktop, drawer di mobile. Padat dan tenang. --}}
@props(['title' => null, 'heading' => null, 'subheading' => null, 'breadcrumb' => null])

@php
    $user = auth()->user();
    $isOwner = $user->isOwner();
    $counts = $adminCounts ?? [];

    $groups = [
        null => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
        ],
        'Operasional' => [
            ['label' => 'Booking', 'route' => 'admin.bookings.index', 'match' => 'admin.bookings.*', 'icon' => 'calendar-check', 'count' => $counts['bookings'] ?? 0],
            ['label' => 'Kontrak', 'route' => 'admin.leases.index', 'match' => 'admin.leases.*', 'icon' => 'file-signature'],
            ['label' => 'Tagihan', 'route' => 'admin.invoices.index', 'match' => 'admin.invoices.*', 'icon' => 'receipt-text'],
            ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'match' => 'admin.payments.*', 'icon' => 'wallet', 'count' => $counts['payments'] ?? 0],
        ],
        'Properti' => [
            ['label' => 'Gedung & Kamar', 'route' => 'admin.properties.index', 'match' => ['admin.properties.*', 'admin.rooms.*'], 'icon' => 'building-2'],
            ['label' => 'Pengguna', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'users', 'count' => $isOwner ? ($counts['users'] ?? 0) : 0],
        ],
        'Pemilik' => [
            ['label' => 'Fasilitas', 'route' => 'admin.facilities.index', 'match' => 'admin.facilities.*', 'icon' => 'sofa', 'owner' => true],
            ['label' => 'Instansi', 'route' => 'admin.instances.index', 'match' => 'admin.instances.*', 'icon' => 'graduation-cap', 'owner' => true],
            ['label' => 'Ulasan', 'route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'icon' => 'star', 'owner' => true],
            ['label' => 'Pengajuan kost', 'route' => 'admin.property-submissions.index', 'match' => 'admin.property-submissions.*', 'icon' => 'inbox', 'owner' => true, 'count' => $counts['propertySubmissions'] ?? 0],
            ['label' => 'Laporan', 'route' => 'admin.reports.payments', 'match' => 'admin.reports.*', 'icon' => 'chart-column', 'owner' => true],
        ],
    ];

    $groups = collect($groups)
        ->map(fn ($items) => array_values(array_filter($items, fn ($i) => Route::has($i['route']) && (empty($i['owner']) || $isOwner))))
        ->filter(fn ($items) => count($items));
@endphp

<x-layouts.base :title="($title ?? $heading).' · Admin'">
    <div x-data="{ drawer: false }" class="min-h-[100dvh] lg:pl-64">
        {{-- Sidebar --}}
        <div x-show="drawer" x-cloak x-on:click="drawer = false" x-transition.opacity class="fixed inset-0 z-drawer bg-tegel-950/50 lg:hidden"></div>

        <aside :class="drawer ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-drawer flex w-64 -translate-x-full flex-col overflow-hidden bg-tegel-900 text-tegel-100 transition-transform duration-200 ease-out lg:translate-x-0"
            aria-label="Menu admin">
            <div class="bg-tegel pointer-events-none absolute inset-x-0 bottom-0 h-48 opacity-[0.08] [mask-image:linear-gradient(to_top,black,transparent)]" aria-hidden="true"></div>

            <div class="relative flex h-16 shrink-0 items-center justify-between px-5">
                <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard admin"><x-application-logo tone="light" /></a>
                <button type="button" x-on:click="drawer = false" class="rounded-lg p-1.5 text-tegel-200 hover:bg-white/10 lg:hidden">
                    <span class="sr-only">Tutup menu</span>
                    <x-lucide-x class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
                </button>
            </div>

            <nav class="relative flex-1 space-y-6 overflow-y-auto px-3 py-4">
                @foreach ($groups as $group => $items)
                    <div>
                        @if ($group)
                            <p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-tegel-300">{{ $group }}</p>
                        @endif
                        <ul class="space-y-0.5">
                            @foreach ($items as $item)
                                @php $active = request()->routeIs(...(array) $item['match']); @endphp
                                <li>
                                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                                        @class([
                                            'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                            'bg-white/10 text-white' => $active,
                                            'text-tegel-100 hover:bg-white/5 hover:text-white' => ! $active,
                                        ])>
                                        <x-dynamic-component :component="'lucide-'.$item['icon']" @class(['h-[18px] w-[18px] shrink-0', 'text-kuningan-300' => $active, 'text-tegel-300 group-hover:text-tegel-100' => ! $active]) stroke-width="1.75" aria-hidden="true" />
                                        <span class="flex-1">{{ $item['label'] }}</span>
                                        @if (! empty($item['count']))
                                            <span class="num rounded-full bg-kuningan-300 px-1.5 py-px text-[11px] font-bold text-tegel-950">
                                                {{ $item['count'] }}<span class="sr-only"> menunggu</span>
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="relative border-t border-white/10 p-4 text-xs text-tegel-200">
                Masuk sebagai <span class="font-semibold text-white">{{ $user->roleLabel() }}</span>
            </div>
        </aside>

        {{-- Topbar --}}
        <header class="sticky top-0 z-nav flex h-16 items-center justify-between gap-4 border-b border-kapur-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" x-on:click="drawer = true" class="-ml-1.5 rounded-lg p-1.5 text-ink-700 hover:bg-kapur-100 lg:hidden">
                    <span class="sr-only">Buka menu</span>
                    <x-lucide-panel-left class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
                </button>
                @if ($breadcrumb)
                    <x-breadcrumb :items="$breadcrumb" class="hidden truncate sm:block" />
                @endif
            </div>
            <div class="flex items-center gap-1">
                @if (Route::has('kost.index'))
                    <a href="{{ route('kost.index') }}" target="_blank" class="hidden items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm font-medium text-ink-500 hover:bg-kapur-100 hover:text-ink-900 sm:inline-flex">
                        <x-lucide-external-link class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />
                        Lihat situs
                    </a>
                @endif
                <x-user-menu />
            </div>
        </header>

        <main id="konten" class="px-4 py-6 sm:px-6 lg:px-8">
            @if ($heading)
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="font-display text-2xl font-semibold tracking-tight">{{ $heading }}</h1>
                        @if ($subheading)
                            <p class="mt-1 text-sm text-ink-500">{{ $subheading }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="flex shrink-0 flex-wrap gap-2">{{ $actions }}</div>
                    @endisset
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</x-layouts.base>
