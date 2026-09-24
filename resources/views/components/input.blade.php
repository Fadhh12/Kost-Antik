@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'id' => null,
    'prefix' => null,
])

@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?? 'f-'.str_replace('.', '-', $key);
    $hasError = $errors->has($key);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
    $current = $type === 'password' ? null : old($key, $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'min-w-0']) }}>
    @if ($label)
        <label for="{{ $id }}" class="label">
            {{ $label }}
            @if ($attributes->has('required'))<span class="text-danger" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if ($prefix)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-ink-500">{{ $prefix }}</span>
        @endif
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if (! is_null($current)) value="{{ $current }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('class')->class(['field', 'field-error' => $hasError, 'pl-10' => $prefix]) }}
        >
    </div>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-ink-500">{{ $hint }}</p>
    @endif
    <x-input-error :for="$key" :id="$id.'-error'" />
</div>
