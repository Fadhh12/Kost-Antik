{{-- Kartu kost di katalog & landing. Butuh withCatalogStats() + coverImage. --}}
@props(['property', 'large' => false])

@php
    $available = (int) $property->available_rooms_count;
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-xl border border-kapur-200 bg-white transition duration-200 ease-out hover:-translate-y-0.5 hover:border-tegel-200 hover:shadow-lift']) }}>
    <div @class(['relative overflow-hidden bg-kapur-100', 'aspect-[4/3]' => ! $large, 'aspect-[16/10] lg:aspect-auto lg:flex-1' => $large])>
        <img src="{{ $property->cover_url }}" alt="Foto {{ $property->name }}" loading="lazy"
            class="absolute inset-0 h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]">
    </div>

    <div @class(['flex flex-col gap-3 p-4 sm:p-5', 'flex-1' => ! $large])>
        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge tone="info" :icon="$property->gender_target->icon()" :label="'Kost '.strtolower($property->gender_target->label())" size="sm" />
            @if ($available > 0)
                <x-status-badge tone="success" icon="door-open" :label="'Tersedia '.$available.' kamar'" size="sm" />
            @else
                <x-status-badge tone="neutral" icon="door-closed" label="Penuh" size="sm" />
            @endif
        </div>

        <div class="min-w-0">
            <h3 @class(['font-display font-semibold leading-snug text-ink-900', 'text-lg' => ! $large, 'text-2xl' => $large])>
                <a href="{{ route('kost.show', $property) }}" class="after:absolute after:inset-0 focus:outline-none">
                    {{ $property->name }}
                </a>
            </h3>
            <p class="mt-1 flex items-center gap-1.5 truncate text-sm text-ink-500">
                <x-lucide-map-pin class="h-3.5 w-3.5 shrink-0" stroke-width="1.75" aria-hidden="true" />
                {{ $property->city }}, {{ $property->address }}
            </p>
        </div>

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-kapur-100 pt-3">
            <div>
                <p class="text-xs text-ink-500">Mulai dari</p>
                <p class="num whitespace-nowrap font-display text-lg font-semibold text-tegel-800"><x-money :amount="$property->starting_price" /><span class="text-sm font-medium text-ink-500">/bln</span></p>
            </div>
            <x-rating :value="$property->rating_avg" :count="$property->reviews_count" :compact="true" class="mb-0.5" />
        </div>
    </div>
</article>
