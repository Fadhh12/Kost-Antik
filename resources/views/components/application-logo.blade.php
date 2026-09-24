@props(['tone' => 'dark', 'mark' => false])

@php
    $text = $tone === 'light' ? 'text-kapur-50' : 'text-tegel-800';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-2.5 {$text}"]) }}>
    {{-- Tanda: satu ubin tegel kunci. --}}
    <svg viewBox="0 0 32 32" class="h-8 w-8 shrink-0" aria-hidden="true">
        <rect width="32" height="32" rx="7" class="fill-tegel-700" />
        <g fill="none" class="stroke-kuningan-300" stroke-width="1.6">
            <path d="M4 12A8 8 0 0 0 12 4M20 4a8 8 0 0 0 8 8M28 20a8 8 0 0 0-8 8M12 28a8 8 0 0 0-8-8" />
            <path d="M16 9.5l6.5 6.5-6.5 6.5L9.5 16z" />
        </g>
        <circle cx="16" cy="16" r="1.8" class="fill-kuningan-300" />
    </svg>
    @unless ($mark)
        <span class="font-display text-lg font-bold leading-none tracking-tight">Kost Antik</span>
    @endunless
</span>
