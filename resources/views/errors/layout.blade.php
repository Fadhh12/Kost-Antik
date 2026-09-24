<x-layouts.base :title="$title">
    <main id="konten" class="relative flex min-h-[100dvh] items-center overflow-hidden px-4 py-16">
        <div class="bg-tegel-light pointer-events-none absolute inset-0 opacity-[0.06]" aria-hidden="true"></div>

        <div class="relative mx-auto grid w-full max-w-4xl items-center gap-12 md:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            {{-- Ubin retak: kode error di tengah satu tegel besar. --}}
            <div class="relative mx-auto aspect-square w-56 sm:w-72">
                <div class="absolute inset-0 rotate-3 rounded-2xl bg-tegel-800 shadow-lift"></div>
                <div class="bg-tegel absolute inset-0 rotate-3 rounded-2xl opacity-25"></div>
                <div class="absolute inset-0 -rotate-2 rounded-2xl border border-tegel-100 bg-white/95 shadow-lift"></div>
                <div class="absolute inset-0 flex -rotate-2 flex-col items-center justify-center">
                    <span class="num font-display text-7xl font-bold tracking-tight text-tegel-800 sm:text-8xl">{{ $code }}</span>
                    <span class="mt-2 h-1 w-10 rounded-full bg-kuningan-500"></span>
                </div>
            </div>

            <div class="text-center md:text-left">
                <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $heading }}</h1>
                <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-ink-500 md:mx-0">{{ $message }}</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3 md:justify-start">
                    <x-button href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" variant="secondary" icon="arrow-left">Kembali</x-button>
                    <x-button href="{{ url('/') }}" icon="house">Ke beranda</x-button>
                </div>
            </div>
        </div>
    </main>
</x-layouts.base>
