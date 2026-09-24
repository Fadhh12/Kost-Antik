{{-- Galeri 1 besar + 4 kecil dengan lightbox (layar P3). --}}
@props(['images', 'title'])

@php
    $urls = $images->map(fn ($i) => $i->url)->values();
    $count = $urls->count();
@endphp

<div x-data="{
        open: false,
        index: 0,
        images: @js($urls),
        show(i) { this.index = i; this.open = true; document.body.classList.add('overflow-y-hidden'); this.$nextTick(() => this.$refs.close.focus()) },
        hide() { this.open = false; document.body.classList.remove('overflow-y-hidden') },
        next() { this.index = (this.index + 1) % this.images.length },
        prev() { this.index = (this.index - 1 + this.images.length) % this.images.length },
    }"
    x-on:keydown.escape.window="open && hide()"
    x-on:keydown.arrow-right.window="open && next()"
    x-on:keydown.arrow-left.window="open && prev()"
    {{ $attributes }}>

    @if ($count === 0)
        <div class="relative aspect-[16/9] overflow-hidden rounded-2xl bg-tegel-800">
            <div class="bg-tegel absolute inset-0 opacity-20"></div>
        </div>
    @else
        <div class="grid gap-2 overflow-hidden rounded-2xl sm:grid-cols-4 sm:grid-rows-2 sm:gap-3 sm:rounded-2xl">
            @foreach ($urls->take(5) as $i => $url)
                @php
                    // Isi grid 4 kolom x 2 baris tanpa sel kosong untuk 1-5 foto.
                    $span = match (true) {
                        $i === 0 => 'aspect-[4/3] sm:aspect-auto sm:min-h-[26rem] sm:row-span-2 '.($count === 1 ? 'sm:col-span-4' : 'sm:col-span-2'),
                        $count === 2 => 'hidden sm:block sm:col-span-2 sm:row-span-2',
                        $count === 3, $count === 4 && $i === 1 => 'hidden sm:block sm:col-span-2',
                        default => 'hidden sm:block aspect-[4/3] sm:aspect-auto',
                    };
                @endphp
                <button type="button" x-on:click="show({{ $i }})"
                    class="group relative overflow-hidden bg-kapur-100 focus-visible:ring-inset {{ $span }}">
                    <img src="{{ $url }}" alt="Foto {{ $title }} {{ $i + 1 }} dari {{ $count }}" @if ($i > 0) loading="lazy" @endif
                        class="absolute inset-0 h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]">
                    @if ($i === 4 && $count > 5)
                        <span class="absolute inset-0 flex items-center justify-center bg-tegel-950/55 font-semibold text-white">+{{ $count - 5 }} foto</span>
                    @endif
                </button>
            @endforeach
        </div>
        <button type="button" x-on:click="show(0)" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-tegel-700 hover:text-tegel-900 sm:hidden">
            <x-lucide-images class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />
            Lihat {{ $count }} foto
        </button>
    @endif

    {{-- Lightbox --}}
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-modal flex flex-col bg-tegel-950/95" role="dialog" aria-modal="true" aria-label="Galeri foto {{ $title }}">
        <div class="flex items-center justify-between p-4 text-white">
            <p class="num text-sm" x-text="(index + 1) + ' / ' + images.length"></p>
            <button type="button" x-ref="close" x-on:click="hide()" class="rounded-lg p-2 hover:bg-white/10">
                <span class="sr-only">Tutup galeri</span>
                <x-lucide-x class="h-6 w-6" stroke-width="1.75" aria-hidden="true" />
            </button>
        </div>
        <div class="relative flex flex-1 items-center justify-center px-4 pb-8 sm:px-16" x-on:click.self="hide()">
            <img :src="images[index]" :alt="'Foto {{ $title }} ' + (index + 1)" class="max-h-full max-w-full rounded-xl object-contain">
            <button type="button" x-on:click="prev()" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:left-4">
                <span class="sr-only">Foto sebelumnya</span>
                <x-lucide-chevron-left class="h-6 w-6" stroke-width="2" aria-hidden="true" />
            </button>
            <button type="button" x-on:click="next()" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:right-4">
                <span class="sr-only">Foto berikutnya</span>
                <x-lucide-chevron-right class="h-6 w-6" stroke-width="2" aria-hidden="true" />
            </button>
        </div>
    </div>
</div>
