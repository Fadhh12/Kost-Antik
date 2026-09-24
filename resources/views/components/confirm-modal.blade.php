{{--
    Modal konfirmasi untuk aksi penting/destruktif. Selalu menulis konsekuensinya.
    Buka dengan: x-on:click="$dispatch('open-modal', 'nama')".
    Set :reason="true" untuk aksi yang wajib alasan (tolak/terminasi).
--}}
@props([
    'name',
    'title',
    'action',
    'method' => 'PATCH',
    'confirm' => 'Ya, lanjutkan',
    'variant' => 'danger',
    'reason' => false,
    'reasonLabel' => 'Alasan',
    'icon' => null,
])

@php
    $icon = $icon ?? ($variant === 'danger' ? 'triangle-alert' : 'circle-check');
    $iconTone = $variant === 'danger' ? 'bg-danger-soft text-danger' : 'bg-tegel-50 text-tegel-700';
@endphp

<x-modal :name="$name" max-width="md">
    <form method="POST" action="{{ $action }}" class="p-5 sm:p-6">
        @csrf
        @unless (strtoupper($method) === 'POST')
            @method($method)
        @endunless

        <div class="flex gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconTone }}">
                <x-dynamic-component :component="'lucide-'.$icon" class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="font-display text-lg font-semibold text-ink-900">{{ $title }}</h2>
                <div class="mt-1.5 text-sm leading-relaxed text-ink-500">{{ $slot }}</div>
            </div>
        </div>

        @if ($reason)
            <x-textarea name="reason" :id="'reason-'.$name" :label="$reasonLabel" rows="3" maxlength="500" required minlength="10"
                hint="Minimal 10 karakter. Alasan ini akan terlihat oleh penyewa." class="mt-5" />
        @endif

        @isset($fields)
            <div class="mt-5 space-y-4">{{ $fields }}</div>
        @endisset

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-button variant="secondary" x-on:click="$dispatch('close')">Batal</x-button>
            <x-button type="submit" :variant="$variant === 'danger' ? 'danger' : 'primary'">{{ $confirm }}</x-button>
        </div>
    </form>
</x-modal>
