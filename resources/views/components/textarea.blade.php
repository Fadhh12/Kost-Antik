@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'rows' => 4,
    'id' => null,
    'maxlength' => null,
])

@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?? 'f-'.str_replace('.', '-', $key);
    $hasError = $errors->has($key);
    $current = old($key, $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'min-w-0']) }}
    @if ($maxlength) x-data="{ count: {{ mb_strlen((string) $current) }} }" @endif>
    @if ($label)
        <label for="{{ $id }}" class="label">
            {{ $label }}
            @if ($attributes->has('required'))<span class="text-danger" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($maxlength) maxlength="{{ $maxlength }}" x-on:input="count = $event.target.value.length" @endif
        @if ($hint) aria-describedby="{{ $id }}-hint" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except('class')->class(['field resize-y', 'field-error' => $hasError]) }}
    >{{ $current }}</textarea>

    <div class="mt-1.5 flex items-start justify-between gap-3">
        <div class="min-w-0">
            @if ($hint)
                <p id="{{ $id }}-hint" class="text-xs text-ink-500">{{ $hint }}</p>
            @endif
            <x-input-error :for="$key" class="!mt-0" />
        </div>
        @if ($maxlength)
            <p class="num shrink-0 text-xs text-ink-500" aria-live="polite"><span x-text="count"></span>/{{ $maxlength }}</p>
        @endif
    </div>
</div>
