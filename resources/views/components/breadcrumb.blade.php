{{-- :items="['Gedung' => route('admin.properties.index'), 'Kost Melati' => null]" --}}
@props(['items' => []])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-sm']) }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-ink-500">
        @foreach ($items as $label => $url)
            <li class="flex items-center gap-1.5">
                @if (! $loop->first)
                    <x-lucide-chevron-right class="h-3.5 w-3.5 text-ink-300" stroke-width="2" aria-hidden="true" />
                @endif
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="transition hover:text-tegel-700">{{ $label }}</a>
                @else
                    <span @if ($loop->last) aria-current="page" class="font-medium text-ink-900" @endif>{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
