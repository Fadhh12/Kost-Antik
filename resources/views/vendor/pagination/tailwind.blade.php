@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-ink-500 sm:block">
            Menampilkan <span class="num font-semibold text-ink-900">{{ $paginator->firstItem() }}</span>-<span class="num font-semibold text-ink-900">{{ $paginator->lastItem() }}</span>
            dari <span class="num font-semibold text-ink-900">{{ $paginator->total() }}</span>
        </p>

        @php
            $base = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-semibold transition';
            $idle = 'text-ink-700 hover:bg-white hover:shadow-tile';
        @endphp

        <div class="flex w-full items-center justify-between gap-1 sm:w-auto sm:justify-end">
            @if ($paginator->onFirstPage())
                <span class="{{ $base }} cursor-not-allowed text-ink-300" aria-disabled="true">
                    <x-lucide-chevron-left class="h-4 w-4" stroke-width="2" aria-hidden="true" /><span class="sr-only sm:not-sr-only sm:ml-1">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $base }} {{ $idle }}">
                    <x-lucide-chevron-left class="h-4 w-4" stroke-width="2" aria-hidden="true" /><span class="sr-only sm:not-sr-only sm:ml-1">Sebelumnya</span>
                </a>
            @endif

            <span class="num text-sm text-ink-500 sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $base }} text-ink-400">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $base }} num bg-tegel-700 text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $base }} num {{ $idle }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $base }} {{ $idle }}">
                    <span class="sr-only sm:not-sr-only sm:mr-1">Berikutnya</span><x-lucide-chevron-right class="h-4 w-4" stroke-width="2" aria-hidden="true" />
                </a>
            @else
                <span class="{{ $base }} cursor-not-allowed text-ink-300" aria-disabled="true">
                    <span class="sr-only sm:not-sr-only sm:mr-1">Berikutnya</span><x-lucide-chevron-right class="h-4 w-4" stroke-width="2" aria-hidden="true" />
                </span>
            @endif
        </div>
    </nav>
@endif
