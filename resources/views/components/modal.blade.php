@props([
    'name',
    'show' => false,
    'maxWidth' => 'lg',
    'title' => null,
    'description' => null,
])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '4xl' => 'sm:max-w-4xl',
    ][$maxWidth];
    $titleId = 'modal-'.$name.'-title';
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)].filter(el => ! el.hasAttribute('disabled') && el.offsetParent !== null)
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            setTimeout(() => (firstFocusable() || $el).focus(), 60);
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 z-modal flex items-end justify-center overflow-y-auto px-0 sm:items-center sm:px-4 sm:py-8"
    style="display: {{ $show ? 'flex' : 'none' }};"
    role="dialog"
    aria-modal="true"
    @if ($title) aria-labelledby="{{ $titleId }}" @endif
>
    <div x-show="show" x-on:click="show = false"
        class="fixed inset-0 bg-tegel-950/55 backdrop-blur-[2px]"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <div x-show="show"
        {{ $attributes->merge(['class' => "relative w-full {$maxWidth} overflow-hidden rounded-t-2xl bg-white shadow-lift sm:rounded-xl"]) }}
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-2">
        @if ($title)
            <div class="flex items-start justify-between gap-4 border-b border-kapur-200 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="{{ $titleId }}" class="font-display text-lg font-semibold text-ink-900">{{ $title }}</h2>
                    @if ($description)
                        <p class="mt-0.5 text-sm text-ink-500">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" x-on:click="show = false" class="-mr-2 rounded-lg p-2 text-ink-500 transition hover:bg-kapur-100 hover:text-ink-900">
                    <span class="sr-only">Tutup</span>
                    <x-lucide-x class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
                </button>
            </div>
        @endif
        {{ $slot }}
    </div>
</div>
