{{--
    Tab berbasis link (state di URL agar bisa dibagikan).
    :tabs="['pending' => ['label' => 'Menunggu', 'url' => ..., 'count' => 3], ...]" :active="'pending'"
--}}
@props(['tabs' => [], 'active' => null])

<nav {{ $attributes->merge(['class' => '-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0']) }} aria-label="Tab">
    <div class="flex min-w-max gap-1 border-b border-kapur-200">
        @foreach ($tabs as $key => $tab)
            @php $isActive = (string) $key === (string) $active; @endphp
            <a href="{{ $tab['url'] }}" @if ($isActive) aria-current="page" @endif
                @class([
                    '-mb-px inline-flex items-center gap-2 border-b-2 px-3 py-2.5 text-sm font-semibold transition',
                    'border-tegel-700 text-tegel-800' => $isActive,
                    'border-transparent text-ink-500 hover:border-kapur-300 hover:text-ink-900' => ! $isActive,
                ])>
                {{ $tab['label'] }}
                @if (isset($tab['count']))
                    <span @class([
                        'num rounded-full px-1.5 py-px text-[11px]',
                        'bg-tegel-700 text-white' => $isActive,
                        'bg-kapur-100 text-ink-500' => ! $isActive,
                    ])>{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>
</nav>
