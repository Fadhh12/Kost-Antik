{{--
    Badge status. Terima enum (punya label(), tone(), icon()) lewat :status,
    atau tone/label/icon manual. Tone: success | warning | danger | neutral | info.
--}}
@props(['status' => null, 'tone' => null, 'label' => null, 'icon' => null, 'size' => 'md'])

@php
    $tone = $tone ?? $status?->tone() ?? 'neutral';
    $label = $label ?? $status?->label() ?? '';
    $icon = $icon ?? $status?->icon();

    $tones = [
        'success' => 'bg-success-soft text-success ring-success-line',
        'warning' => 'bg-warning-soft text-warning ring-warning-line',
        'danger' => 'bg-danger-soft text-danger ring-danger-line',
        'neutral' => 'bg-neutral-soft text-neutral ring-neutral-line',
        'info' => 'bg-tegel-50 text-tegel-700 ring-tegel-100',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px] gap-1',
        'md' => 'px-2.5 py-1 text-xs gap-1.5',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center whitespace-nowrap rounded-full font-semibold ring-1 ring-inset '.($tones[$tone] ?? $tones['neutral']).' '.($sizes[$size] ?? $sizes['md'])]) }}>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="h-3.5 w-3.5 shrink-0" stroke-width="2" aria-hidden="true" />
    @endif
    {{ $label }}{{ $slot }}
</span>
