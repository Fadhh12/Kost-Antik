<x-layouts.public>
    {{-- Hero: teks + pencarian di kiri, foto di kanan --}}
    <section class="relative overflow-hidden">
        <div class="bg-tegel-light pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 opacity-[0.07] lg:block" aria-hidden="true"></div>

        <div class="relative mx-auto grid max-w-page items-center gap-10 px-4 pb-16 pt-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-14 lg:px-8 lg:pb-24 lg:pt-16">
            <div class="animate-rise">
                <h1 class="font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ink-900 sm:text-5xl xl:text-[3.4rem]">
                    Kamar yang jelas, tagihan yang <span class="text-tegel-700">rapi</span>.
                </h1>
                <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-500">
                    Cek kamar yang benar-benar kosong di Bekasi dan Cikarang, ajukan sewa online, dan bayar dengan bukti yang tercatat.
                </p>

                {{-- Pencarian kota + gender --}}
                <form action="{{ route('kost.index') }}" method="GET" data-no-lock
                    class="mt-8 grid gap-3 rounded-2xl border border-kapur-200 bg-white p-3 shadow-lift sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                    <div>
                        <label for="hero-city" class="px-1 text-xs font-semibold text-ink-500">Kota</label>
                        <select id="hero-city" name="city" class="field mt-1 border-transparent bg-kapur-50 shadow-none">
                            <option value="">Semua kota</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="hero-gender" class="px-1 text-xs font-semibold text-ink-500">Untuk</label>
                        <select id="hero-gender" name="gender" class="field mt-1 border-transparent bg-kapur-50 shadow-none">
                            <option value="">Putra, putri, campur</option>
                            @foreach (\App\Enums\GenderTarget::cases() as $target)
                                <option value="{{ $target->value }}">Kost {{ strtolower($target->label()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="available" value="1">
                    <x-button type="submit" size="lg" icon="search">Cari kost</x-button>
                </form>
            </div>

            <div class="relative animate-rise [animation-delay:120ms]">
                <div class="absolute -inset-3 rounded-[1.4rem] bg-tegel-800 sm:-inset-4" aria-hidden="true">
                    <div class="bg-tegel h-full w-full rounded-[1.4rem] opacity-25"></div>
                </div>
                <img src="{{ asset('images/placeholders/kost-1.svg') }}" alt="Ilustrasi fasad rumah kost dengan lantai tegel kunci"
                    class="relative aspect-[4/3] w-full rounded-2xl object-cover" fetchpriority="high">
            </div>
        </div>
    </section>

    <x-tegel-divider class="opacity-60" />

    {{-- Kost unggulan: satu besar + dua bertumpuk --}}
    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-page px-4 py-20 sm:px-6 lg:px-8" aria-labelledby="unggulan">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="unggulan" class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Masih ada kamar kosong</h2>
                <x-button :href="route('kost.index')" variant="secondary" icon-right="arrow-right">Lihat semua</x-button>
            </div>

            <div class="mt-10 grid gap-5 lg:grid-cols-12">
                <x-property-card :property="$featured->first()" :large="true" class="lg:col-span-7" data-reveal />
                <div class="grid gap-5 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-1">
                    @foreach ($featured->slice(1) as $property)
                        <x-property-card :property="$property" data-reveal style="--reveal-i: {{ $loop->iteration }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Alasan memilih: bento 3 sel dengan latar berbeda --}}
    <section class="bg-white py-20" aria-labelledby="alasan">
        <div class="mx-auto max-w-page px-4 sm:px-6 lg:px-8">
            <h2 id="alasan" class="max-w-2xl font-display text-3xl font-semibold tracking-tight sm:text-4xl">Tinggal tenang, tanpa catatan yang tercecer</h2>

            <div class="mt-10 grid gap-5 lg:grid-cols-3 lg:grid-rows-2">
                <div class="relative overflow-hidden rounded-2xl bg-tegel-800 p-7 text-tegel-100 lg:row-span-2" data-reveal>
                    <div class="bg-tegel pointer-events-none absolute inset-0 opacity-[0.1]" aria-hidden="true"></div>
                    <div class="relative flex h-full flex-col">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-kuningan-300">
                            <x-lucide-receipt-text class="h-6 w-6" stroke-width="1.75" aria-hidden="true" />
                        </span>
                        <h3 class="mt-6 font-display text-2xl font-semibold text-white">Tagihan transparan</h3>
                        <p class="mt-3 max-w-sm leading-relaxed">
                            Tagihan tiap bulan terbit otomatis saat kontrak dibuat. Kamu unggah bukti transfer, pengelola memverifikasi, dan riwayatnya tersimpan.
                        </p>
                        <ul class="mt-auto space-y-2.5 pt-8 text-sm">
                            <li class="flex items-center gap-2.5"><x-lucide-check class="h-4 w-4 text-kuningan-300" stroke-width="2.25" aria-hidden="true" /> Jatuh tempo jelas, ada masa tenggang {{ config('kost.grace_days') }} hari</li>
                            <li class="flex items-center gap-2.5"><x-lucide-check class="h-4 w-4 text-kuningan-300" stroke-width="2.25" aria-hidden="true" /> Bukti bayar hanya bisa dilihat kamu dan pengelola</li>
                            <li class="flex items-center gap-2.5"><x-lucide-check class="h-4 w-4 text-kuningan-300" stroke-width="2.25" aria-hidden="true" /> Riwayat kontrak dan pembayaran selalu tersedia</li>
                        </ul>
                    </div>
                </div>

                <div class="relative min-h-[16rem] overflow-hidden rounded-2xl lg:col-span-2" data-reveal style="--reveal-i: 1">
                    <img src="{{ asset('images/placeholders/kamar-3.svg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-r from-tegel-950/85 via-tegel-950/50 to-transparent"></div>
                    <div class="relative max-w-md p-7">
                        <h3 class="font-display text-2xl font-semibold text-white">Fasilitas tertulis per kamar</h3>
                        <p class="mt-3 leading-relaxed text-tegel-100">AC, kamar mandi dalam, meja belajar. Semua tercantum per kamar lengkap dengan luas dan lantainya, jadi tidak ada kejutan saat datang.</p>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-kapur-200 bg-kapur-50 p-7 lg:col-span-2" data-reveal style="--reveal-i: 2">
                    <div class="bg-tegel-light pointer-events-none absolute -right-10 top-0 h-full w-1/2 opacity-[0.12] [mask-image:linear-gradient(to_left,black,transparent)]" aria-hidden="true"></div>
                    <div class="relative">
                        <h3 class="font-display text-2xl font-semibold">Dekat kampus dan kawasan industri</h3>
                        <p class="mt-3 max-w-lg leading-relaxed text-ink-500">Gedung kami tersebar di Bekasi dan Cikarang, dekat dengan tempat penghuni kuliah dan bekerja.</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($instances as $name)
                                <span class="rounded-full border border-kapur-300 bg-white px-3 py-1 text-sm text-ink-700">{{ $name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cara sewa: memang berurutan --}}
    <section id="cara-sewa" class="mx-auto max-w-page scroll-mt-20 px-4 py-20 sm:px-6 lg:px-8" aria-labelledby="cara-sewa-judul">
        <h2 id="cara-sewa-judul" class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Dari lihat kamar sampai pegang kunci</h2>

        @php
            $steps = [
                ['search', 'Pilih kamar', 'Saring berdasarkan kota, harga, dan kost putra/putri. Lihat status tiap kamar secara langsung.'],
                ['send', 'Ajukan sewa', 'Daftar, tunggu akun diverifikasi, lalu pilih tanggal masuk dan durasi 1 sampai 12 bulan.'],
                ['key-round', 'Bayar dan pindah', 'Setelah pengelola menyetujui, kontrak dan tagihan terbit. Unggah bukti transfer, ambil kunci.'],
            ];
        @endphp

        <ol class="relative mt-12 grid gap-10 md:grid-cols-3 md:gap-8">
            <div class="tegel-strip absolute left-0 right-0 top-6 hidden opacity-50 md:block" aria-hidden="true"></div>
            @foreach ($steps as [$icon, $title, $text])
                <li class="relative" data-reveal style="--reveal-i: {{ $loop->index }}">
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-tegel-700 text-white shadow-lift ring-8 ring-kapur-50">
                        <x-dynamic-component :component="'lucide-'.$icon" class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
                    </span>
                    <h3 class="mt-5 font-display text-xl font-semibold">{{ $title }}</h3>
                    <p class="mt-2 max-w-sm leading-relaxed text-ink-500">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Ulasan penghuni --}}
    @if ($reviews->isNotEmpty())
        <section class="border-y border-kapur-200 bg-white py-20" aria-labelledby="ulasan">
            <div class="mx-auto grid max-w-page gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
                <div class="lg:col-span-4">
                    <h2 id="ulasan" class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Kata penghuni</h2>
                    <p class="mt-3 max-w-sm leading-relaxed text-ink-500">Ulasan hanya bisa ditulis penghuni yang sudah atau sedang menyewa.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:col-span-8">
                    @foreach ($reviews as $review)
                        <figure @class(['flex flex-col rounded-2xl border border-kapur-200 p-6', 'sm:col-span-2 bg-tegel-50/60' => $loop->first, 'bg-kapur-50' => ! $loop->first]) data-reveal style="--reveal-i: {{ $loop->index }}">
                            <div class="flex gap-0.5" aria-label="Rating {{ $review->rating }} dari 5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <x-lucide-star @class(['h-4 w-4', 'fill-kuningan-500 text-kuningan-500' => $i <= $review->rating, 'text-ink-300' => $i > $review->rating]) stroke-width="1.5" aria-hidden="true" />
                                @endfor
                            </div>
                            <blockquote @class(['mt-4 leading-relaxed text-ink-700 line-clamp-3', 'text-lg' => $loop->first])>&ldquo;{{ $review->comment }}&rdquo;</blockquote>
                            <figcaption class="mt-auto flex items-center gap-3 pt-5">
                                <x-avatar :user="$review->user" size="sm" />
                                <div class="text-sm">
                                    <p class="font-semibold text-ink-900">{{ $review->user->name }}</p>
                                    <p class="text-ink-500">{{ $review->user->instance?->name ?? 'Penghuni' }}, {{ $review->property->name }}</p>
                                </div>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="mx-auto max-w-page px-4 pt-20 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-tegel-800 px-6 py-12 sm:px-12" data-reveal>
            <div class="bg-tegel pointer-events-none absolute inset-y-0 right-0 w-2/3 opacity-[0.12] [mask-image:linear-gradient(to_left,black,transparent)]" aria-hidden="true"></div>
            <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="font-display text-3xl font-semibold tracking-tight text-white">Siap pindah bulan depan?</h2>
                    <p class="mt-2 max-w-lg text-tegel-100">Buat akun sekarang supaya verifikasi selesai sebelum kamu menemukan kamar yang cocok.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-3">
                    @guest
                        <x-button :href="route('register')" variant="brass" size="lg">Daftar</x-button>
                    @endguest
                    <x-button :href="route('kost.index')" variant="light" size="lg" icon="search">Cari kost</x-button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
