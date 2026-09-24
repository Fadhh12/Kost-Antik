@props(['user', 'size' => 'md'])

@php
    $sizes = ['sm' => 'h-8 w-8 text-xs', 'md' => 'h-10 w-10 text-sm', 'lg' => 'h-16 w-16 text-lg', 'xl' => 'h-24 w-24 text-2xl'];
    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

@if ($user->photo_url)
    <img src="{{ $user->photo_url }}" alt="Foto {{ $user->name }}" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover ring-2 ring-white '.$sizes[$size]]) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-tegel-100 font-display font-semibold text-tegel-800 ring-2 ring-white '.$sizes[$size]]) }} aria-hidden="true">
        {{ $initials }}
    </span>
@endif
