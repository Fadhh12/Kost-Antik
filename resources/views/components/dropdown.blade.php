@props(['align' => 'right', 'width' => '56', 'contentClasses' => 'py-1.5 bg-white'])

@php
    $alignmentClasses = match ($align) {
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        default => 'ltr:origin-top-right rtl:origin-top-left end-0',
    };

    $width = match ($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        default => $width,
    };
@endphp

<div class="relative" x-data="{ open: false }"
    x-on:click.outside="open = false"
    x-on:close.stop="open = false"
    x-on:keydown.escape="open && (open = false, $refs.trigger.querySelector('button, a')?.focus())">
    <div x-ref="trigger" x-on:click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute z-drawer mt-2 {{ $width }} {{ $alignmentClasses }}"
        x-on:click="open = false">
        <div class="overflow-hidden rounded-xl border border-kapur-200 shadow-lift {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
