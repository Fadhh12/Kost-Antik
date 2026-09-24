@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconRight' => null,
])

@php
    $variants = [
        'primary' => 'bg-tegel-700 text-white hover:bg-tegel-800 shadow-tile',
        'secondary' => 'bg-white text-ink-900 border border-kapur-300 hover:border-tegel-300 hover:bg-tegel-50 shadow-tile',
        'ghost' => 'text-ink-700 hover:bg-kapur-100 hover:text-ink-900',
        'danger' => 'bg-danger text-white hover:bg-danger-strong shadow-tile',
        'brass' => 'bg-kuningan-300 text-ink-900 hover:bg-kuningan-500 shadow-tile',
        'light' => 'bg-kapur-50 text-tegel-900 hover:bg-white shadow-tile',
    ];

    $sizes = [
        'sm' => 'h-8 px-3 text-xs gap-1.5',
        'md' => 'h-10 px-4 text-sm gap-2',
        'lg' => 'h-12 px-5 text-base gap-2',
    ];

    $iconSize = $size === 'lg' ? 'h-5 w-5' : 'h-4 w-4';
    $gap = $size === 'sm' ? 'gap-1.5' : 'gap-2';

    $classes = 'group/btn relative inline-flex select-none items-center justify-center whitespace-nowrap rounded-lg font-semibold '
        .'transition duration-150 ease-out active:translate-y-px disabled:pointer-events-none disabled:opacity-60 '
        .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="{{ $iconSize }} shrink-0" stroke-width="1.75" aria-hidden="true" />@endif
        {{ $slot }}
        @if ($iconRight)<x-dynamic-component :component="'lucide-'.$iconRight" class="{{ $iconSize }} shrink-0 transition group-hover/btn:translate-x-0.5" stroke-width="1.75" aria-hidden="true" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{-- Spinner tampil saat form dikirim (lihat resources/js/app.js). --}}
        <span class="absolute inset-0 hidden items-center justify-center group-[.is-loading]/btn:flex" aria-hidden="true">
            <x-lucide-loader-circle class="{{ $iconSize }} animate-spin" stroke-width="2" />
        </span>
        <span class="inline-flex items-center {{ $gap }} group-[.is-loading]/btn:invisible">
            @if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="{{ $iconSize }} shrink-0" stroke-width="1.75" aria-hidden="true" />@endif
            {{ $slot }}
            @if ($iconRight)<x-dynamic-component :component="'lucide-'.$iconRight" class="{{ $iconSize }} shrink-0" stroke-width="1.75" aria-hidden="true" />@endif
        </span>
    </button>
@endif
