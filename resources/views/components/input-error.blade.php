@props(['for' => null, 'messages' => null, 'id' => null])

@php
    $messages = $messages ?? ($for ? $errors->get($for) : []);
@endphp

@if ($messages)
    <p @if ($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => 'mt-1.5 flex items-start gap-1.5 text-xs font-medium text-danger']) }}>
        <x-lucide-circle-alert class="mt-px h-3.5 w-3.5 shrink-0" stroke-width="2" aria-hidden="true" />
        <span>{{ implode(' ', (array) $messages) }}</span>
    </p>
@endif
