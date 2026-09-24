{{-- Rating ringkas: bintang kuningan + angka. compact untuk kartu. --}}
@props(['value' => null, 'count' => 0, 'size' => 'sm', 'compact' => false])

@php $text = $size === 'lg' ? 'text-base' : 'text-sm'; @endphp

@if ($count > 0 && $value)
    <span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center gap-1 whitespace-nowrap {$text}"]) }} aria-label="Rating {{ number_format($value, 1, ',', '.') }} dari 5, {{ $count }} ulasan">
        <x-lucide-star class="{{ $size === 'lg' ? 'h-5 w-5' : 'h-4 w-4' }} fill-kuningan-500 text-kuningan-500" stroke-width="1.5" aria-hidden="true" />
        <span class="num font-semibold text-ink-900">{{ number_format($value, 1, ',', '.') }}</span>
        <span class="text-ink-500">({{ $count }}{{ $compact ? '' : ' ulasan' }})</span>
    </span>
@else
    <span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center gap-1 whitespace-nowrap {$text} text-ink-500"]) }}>
        <x-lucide-star class="h-4 w-4 text-ink-300" stroke-width="1.5" aria-hidden="true" />
        {{ $compact ? 'Baru' : 'Belum ada ulasan' }}
    </span>
@endif