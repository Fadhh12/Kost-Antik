@props(['title', 'description' => null, 'icon' => null, 'compact' => false])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center text-center '.($compact ? 'px-4 py-8' : 'px-6 py-14')]) }}>
    {{-- Ilustrasi: satu ubin tegel dengan ikon di tengah. --}}
    <div class="relative mb-5 flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl bg-tegel-50 ring-1 ring-inset ring-tegel-100">
        <div class="bg-tegel-light absolute inset-0 opacity-[0.14]" aria-hidden="true"></div>
        <span class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-white text-tegel-700 shadow-tile">
            <x-dynamic-component :component="'lucide-'.($icon ?? 'inbox')" class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
        </span>
    </div>
    <h3 class="font-display text-lg font-semibold text-ink-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-ink-500">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
