{{-- Panel samping (form kamar, detail booking). Buka: $dispatch('open-slide', 'nama'). --}}
@props(['name', 'title', 'description' => null, 'show' => false, 'width' => 'md'])

@php
    $widths = ['md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl'];
@endphp

<div x-data="{ open: @js($show) }"
    x-on:open-slide.window="$event.detail == '{{ $name }}' ? open = true : null"
    x-on:close-slide.window="$event.detail == '{{ $name }}' ? open = false : null"
    x-on:keydown.escape.window="open = false"
    x-init="$watch('open', v => { document.body.classList.toggle('overflow-y-hidden', v); if (v) setTimeout(() => $refs.panel.querySelector('input,select,textarea,button')?.focus(), 80) })"
    x-show="open" x-cloak
    class="fixed inset-0 z-drawer" role="dialog" aria-modal="true" aria-labelledby="slide-{{ $name }}-title">
    <div x-show="open" x-on:click="open = false" class="fixed inset-0 bg-tegel-950/45"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <div x-ref="panel" x-show="open"
        class="fixed inset-y-0 right-0 flex w-full {{ $widths[$width] ?? $widths['md'] }} flex-col bg-white shadow-lift"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <div class="flex items-start justify-between gap-4 border-b border-kapur-200 px-5 py-4">
            <div>
                <h2 id="slide-{{ $name }}-title" class="font-display text-lg font-semibold">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-sm text-ink-500">{{ $description }}</p>
                @endif
            </div>
            <button type="button" x-on:click="open = false" class="-mr-2 rounded-lg p-2 text-ink-500 hover:bg-kapur-100 hover:text-ink-900">
                <span class="sr-only">Tutup</span>
                <x-lucide-x class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-5">
            {{ $slot }}
        </div>
        @isset($footer)
            <div class="border-t border-kapur-200 bg-kapur-50 px-5 py-3">{{ $footer }}</div>
        @endisset
    </div>
</div>
