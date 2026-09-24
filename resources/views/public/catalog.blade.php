@php
    $activeCount = collect($filters)->except(['sort', 'q'])->count();
    $title = isset($filters['gender'])
        ? 'Kost '.strtolower(\App\Enums\GenderTarget::from($filters['gender'])->label()).(isset($filters['city']) ? ' di '.$filters['city'] : '')
        : (isset($filters['city']) ? 'Kost di '.$filters['city'] : 'Cari kost');
@endphp

@push('head')
    @vite('resources/js/map.js')
@endpush

<x-layouts.public :title="$title" description="Daftar kost di Bekasi dan Cikarang lengkap dengan harga, sisa kamar, fasilitas, dan ulasan penghuni.">
    <div class="mx-auto max-w-page px-4 pb-8 pt-10 sm:px-6 lg:px-8" x-data="{ drawer: false }">
        <div class="flex flex-col gap-2">
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $title }}</h1>
            <p class="text-ink-500">
                <span class="num font-semibold text-ink-900">{{ $properties->total() }}</span> kost ditemukan
            </p>
        </div>

        @if ($mapPoints->isNotEmpty())
            <div class="mt-6 overflow-hidden rounded-xl border border-kapur-200 shadow-tile">
                <div
                    data-map-points
                    data-points="{{ $mapPoints->map(fn ($p) => [
                        'lat' => $p->latitude,
                        'lng' => $p->longitude,
                        'name' => $p->name,
                        'url' => route('kost.show', $p),
                        'thumb' => $p->cover_url,
                        'rating' => $p->rating_avg ? round($p->rating_avg, 1) : null,
                        'reviews' => $p->reviews_count,
                    ])->toJson() }}"
                    class="h-72 w-full sm:h-80"
                ></div>
            </div>
        @endif
        <form id="filter" method="GET" action="{{ route('kost.index') }}" data-no-lock x-ref="form"
            class="mt-8 grid gap-8 lg:grid-cols-[17rem_minmax(0,1fr)]">

            {{-- Filter: sidebar desktop / drawer mobile --}}
            <div>
                <div x-show="drawer" x-cloak x-transition.opacity x-on:click="drawer = false" class="fixed inset-0 z-drawer bg-tegel-950/45 lg:hidden"></div>
                <aside :class="drawer ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
                    class="fixed inset-x-0 bottom-0 z-drawer max-h-[85dvh] translate-y-full overflow-y-auto rounded-t-2xl bg-white p-5 shadow-lift transition-transform duration-300 ease-out lg:sticky lg:top-24 lg:z-auto lg:max-h-none lg:translate-y-0 lg:overflow-visible lg:rounded-xl lg:border lg:border-kapur-200 lg:shadow-none"
                    aria-label="Filter kost">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="font-display text-lg font-semibold">Filter</h2>
                        <div class="flex items-center gap-1">
                            @if ($activeCount)
                                <a href="{{ route('kost.index', array_filter(['q' => $filters['q'] ?? null, 'sort' => $filters['sort'] ?? null])) }}" class="rounded-lg px-2 py-1 text-sm font-semibold text-tegel-700 hover:bg-tegel-50">Reset</a>
                            @endif
                            <button type="button" x-on:click="drawer = false" class="rounded-lg p-1.5 text-ink-500 hover:bg-kapur-100 lg:hidden">
                                <span class="sr-only">Tutup filter</span>
                                <x-lucide-x class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6" x-on:change="if (window.innerWidth >= 1024) $refs.form.requestSubmit()">
                        <div>
                            <label for="f-city" class="label">Kota</label>
                            <select id="f-city" name="city" class="field">
                                <option value="">Semua kota</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>

                        <fieldset>
                            <legend class="label">Peruntukan</legend>
                            <div class="flex flex-wrap gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="gender" value="" class="peer sr-only" @checked(empty($filters['gender']))>
                                    <span class="inline-flex rounded-full border border-kapur-300 px-3 py-1.5 text-sm font-medium text-ink-700 transition peer-checked:border-tegel-700 peer-checked:bg-tegel-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-kuningan-500">Semua</span>
                                </label>
                                @foreach (\App\Enums\GenderTarget::cases() as $target)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="gender" value="{{ $target->value }}" class="peer sr-only" @checked(($filters['gender'] ?? '') === $target->value)>
                                        <span class="inline-flex rounded-full border border-kapur-300 px-3 py-1.5 text-sm font-medium text-ink-700 transition peer-checked:border-tegel-700 peer-checked:bg-tegel-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-kuningan-500">{{ $target->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="label">Harga per bulan</legend>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="f-min" class="sr-only">Harga minimum</label>
                                    <input id="f-min" name="min_price" type="number" inputmode="numeric" min="0" step="50000" placeholder="Min" value="{{ $filters['min_price'] ?? '' }}" class="field num">
                                </div>
                                <div>
                                    <label for="f-max" class="sr-only">Harga maksimum</label>
                                    <input id="f-max" name="max_price" type="number" inputmode="numeric" min="0" step="50000" placeholder="Maks" value="{{ $filters['max_price'] ?? '' }}" class="field num">
                                </div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ([[null, 1000000, '< 1 jt'], [1000000, 1500000, '1 - 1,5 jt'], [1500000, null, '> 1,5 jt']] as [$min, $max, $label])
                                    <a href="{{ route('kost.index', array_filter(array_merge($filters, ['min_price' => $min, 'max_price' => $max]), fn ($v) => $v !== null)) }}"
                                        class="rounded-full bg-kapur-100 px-2.5 py-1 text-xs font-medium text-ink-700 hover:bg-tegel-50 hover:text-tegel-800">{{ $label }}</a>
                                @endforeach
                            </div>
                        </fieldset>

                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-kapur-200 bg-kapur-50 px-3 py-2.5">
                            <span class="text-sm font-medium text-ink-700">Hanya yang ada kamar kosong</span>
                            <input type="checkbox" name="available" value="1" @checked(! empty($filters['available']))
                                class="h-5 w-5 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                        </label>

                        <x-button type="submit" class="w-full lg:hidden">Terapkan filter</x-button>
                    </div>
                </aside>
            </div>

            {{-- Hasil --}}
            <div class="min-w-0">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <label for="f-q" class="sr-only">Cari nama kost, kota, atau alamat</label>
                        <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" stroke-width="2" aria-hidden="true" />
                        <input id="f-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama kost, kota, atau jalan"
                            x-on:input.debounce.400ms="$refs.form.requestSubmit()" class="field h-11 pl-9" autocomplete="off">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" x-on:click="drawer = true" class="inline-flex h-11 items-center gap-2 rounded-lg border border-kapur-300 bg-white px-4 text-sm font-semibold shadow-tile lg:hidden">
                            <x-lucide-sliders-horizontal class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />
                            Filter
                            @if ($activeCount)
                                <span class="num rounded-full bg-tegel-700 px-1.5 text-[11px] text-white">{{ $activeCount }}</span>
                            @endif
                        </button>
                        <label for="f-sort" class="sr-only">Urutkan</label>
                        <select id="f-sort" name="sort" x-on:change="$refs.form.requestSubmit()" class="field h-11 w-auto flex-1 sm:flex-none">
                            @foreach ($sorts as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'terbaru') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($properties->isEmpty())
                    <div class="card mt-6">
                        <x-empty-state icon="search-x" title="Tidak ada kost yang cocok"
                            description="Coba longgarkan rentang harga, pilih kota lain, atau matikan filter kamar kosong.">
                            <x-button :href="route('kost.index')" variant="secondary" icon="rotate-ccw">Reset filter</x-button>
                        </x-empty-state>
                    </div>
                @else
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($properties as $property)
                            <x-property-card :property="$property" />
                        @endforeach
                    </div>
                    <x-pagination :paginator="$properties" />
                @endif
            </div>
        </form>
    </div>
</x-layouts.public>
