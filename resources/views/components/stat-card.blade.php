{{--
    Kartu metrik. Satu warna netral untuk semua kartu (tanpa ikon warna-warni);
    hanya kartu yang butuh perhatian yang diberi tone.
--}}
@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => null, 'href' => null])

@php
    $accent = match ($tone) {
        'danger' => 'text-danger',
        'warning' => 'text-warning',
        'success' => 'text-success',
        default => 'text-ink-900',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card group relative flex flex-col gap-3 p-4 sm:p-5'.($href ? ' transition hover:border-tegel-300 hover:shadow-lift' : '')]) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-ink-500">{{ $label }}</p>
        @if ($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="h-4 w-4 text-ink-400 transition group-hover:text-tegel-600" stroke-width="1.75" aria-hidden="true" />
        @endif
    </div>
    <p class="num font-display text-2xl font-semibold tracking-tight {{ $accent }} sm:text-[1.75rem]">{{ $value }}</p>
    @if ($hint || $slot->isNotEmpty())
        <div class="text-xs text-ink-500">{{ $hint }}{{ $slot }}</div>
    @endif
</{{ $tag }}>
