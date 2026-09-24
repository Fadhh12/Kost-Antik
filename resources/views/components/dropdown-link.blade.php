@props(['icon' => null, 'as' => 'a'])

@php
    $classes = 'flex w-full items-center gap-2.5 px-3.5 py-2 text-start text-sm text-ink-700 transition hover:bg-kapur-50 hover:text-ink-900 focus:bg-kapur-50 focus:outline-none';
@endphp

@if ($as === 'button')
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        @if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="h-4 w-4 text-ink-400" stroke-width="1.75" aria-hidden="true" />@endif
        {{ $slot }}
    </button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="h-4 w-4 text-ink-400" stroke-width="1.75" aria-hidden="true" />@endif
        {{ $slot }}
    </a>
@endif
