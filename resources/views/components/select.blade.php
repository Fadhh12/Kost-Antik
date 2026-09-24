@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'id' => null,
])

@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?? 'f-'.str_replace('.', '-', $key);
    $hasError = $errors->has($key);
    $current = (string) old($key, $value instanceof \BackedEnum ? $value->value : $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'min-w-0']) }}>
    @if ($label)
        <label for="{{ $id }}" class="label">
            {{ $label }}
            @if ($attributes->has('required'))<span class="text-danger" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($hint) aria-describedby="{{ $id }}-hint" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except('class')->class(['field pr-9', 'field-error' => $hasError]) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected($current === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-ink-500">{{ $hint }}</p>
    @endif
    <x-input-error :for="$key" />
</div>
