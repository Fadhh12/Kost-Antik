@props([
    'name',
    'label' => null,
    'accept' => 'image/*',
    'multiple' => false,
    'hint' => null,
    'current' => null,
    'id' => null,
])

@php
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $id ?? 'f-'.str_replace('.', '-', $key);
    $hasError = $errors->has($key) || $errors->has($key.'.*');
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0']) }}
    x-data="{
        files: [],
        dragging: false,
        pick(list) {
            this.files = [...list].map(f => ({
                name: f.name,
                size: (f.size / 1024 / 1024).toFixed(2) + ' MB',
                url: f.type.startsWith('image/') ? URL.createObjectURL(f) : null,
            }));
        },
        drop(e) {
            this.dragging = false;
            if (! e.dataTransfer.files.length) return;
            $refs.input.files = e.dataTransfer.files;
            this.pick($refs.input.files);
        },
    }">
    @if ($label)
        <span class="label" id="{{ $id }}-label">
            {{ $label }}
            @if ($attributes->has('required'))<span class="text-danger" aria-hidden="true">*</span>@endif
        </span>
    @endif

    <label for="{{ $id }}"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="drop($event)"
        :class="dragging ? 'border-tegel-600 bg-tegel-50' : '{{ $hasError ? 'border-danger bg-danger-soft/40' : 'border-kapur-300 bg-white' }}'"
        class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 overflow-hidden rounded-xl border-2 border-dashed px-4 py-6 text-center transition hover:border-tegel-400">
        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-tegel-50 text-tegel-700 transition group-hover:bg-tegel-100">
            <x-lucide-upload class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
        </span>
        <span class="text-sm text-ink-700">
            <span class="font-semibold text-tegel-700">Pilih berkas</span> atau seret ke sini
        </span>
        @if ($hint)
            <span class="text-xs text-ink-500">{{ $hint }}</span>
        @endif
        <input x-ref="input" id="{{ $id }}" name="{{ $name }}" type="file" accept="{{ $accept }}"
            @if ($multiple) multiple @endif
            @if ($attributes->has('required')) required @endif
            aria-labelledby="{{ $id }}-label"
            x-on:change="pick($event.target.files)"
            class="sr-only">
    </label>

    {{-- Pratinjau --}}
    <ul x-show="files.length" x-cloak class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
        <template x-for="file in files" :key="file.name">
            <li class="overflow-hidden rounded-xl border border-kapur-200 bg-white">
                <template x-if="file.url">
                    <img :src="file.url" :alt="'Pratinjau ' + file.name" class="aspect-[4/3] w-full object-cover">
                </template>
                <template x-if="! file.url">
                    <div class="flex aspect-[4/3] items-center justify-center bg-kapur-100 text-ink-500">
                        <x-lucide-file-text class="h-8 w-8" stroke-width="1.5" aria-hidden="true" />
                    </div>
                </template>
                <div class="px-2.5 py-2">
                    <p class="truncate text-xs font-medium text-ink-900" x-text="file.name"></p>
                    <p class="num text-[11px] text-ink-500" x-text="file.size"></p>
                </div>
            </li>
        </template>
    </ul>

    @if ($current)
        <div x-show="! files.length" class="mt-3">
            {{ $current }}
        </div>
    @endif

    <x-input-error :for="$key" />
    <x-input-error :messages="collect($errors->get($key.'.*'))->flatten()->unique()->all()" />
</div>
