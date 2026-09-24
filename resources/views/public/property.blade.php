@php
    use App\Enums\RoomStatus;

    $user = auth()->user();
    $canApply = Route::has('app.bookings.store');
    $roomsJson = $rooms->map(fn ($r) => [
        'id' => $r->id,
        'code' => $r->code,
        'floor' => $r->floor,
        'size' => $r->size_m2,
        'price' => $r->monthly_price,
        'available' => $r->status === RoomStatus::Available,
    ])->keyBy('id');
    $preselect = (int) request('kamar');
    $ruleLines = collect(preg_split('/\r?\n/', (string) $property->rules))->map(fn ($l) => trim($l))->filter();
@endphp

<x-layouts.public :title="$property->name.', '.$property->city"
    :description="\Illuminate\Support\Str::limit(strip_tags($property->description ?? ''), 155) ?: 'Kost '.strtolower($property->gender_target->label()).' di '.$property->city.' mulai '.rupiah($startingPrice).' per bulan.'"
    :image="$property->cover_url">
    @push('head')
        @vite('resources/js/map.js')
    @endpush

    <div x-data="{
            rooms: @js($roomsJson),
            room: null,
            duration: 6,
            start: '{{ today()->addDays(7)->toDateString() }}',
            pick(id) { this.room = this.rooms[id]; $dispatch('open-modal', 'ajukan-sewa') },
            get total() { return this.room ? this.room.price * this.duration : 0 },
            get endDate() {
                if (! this.start) return '-';
                const d = new Date(this.start + 'T00:00:00');
                const day = d.getDate();
                d.setMonth(d.getMonth() + Number(this.duration));
                if (d.getDate() !== day) d.setDate(0);
                d.setDate(d.getDate() - 1);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            },
            rupiah(n) { return 'Rp' + Number(n).toLocaleString('id-ID') },
        }"
        @if ($preselect && $roomsJson->has($preselect) && $user && ! $blocker && $canApply)
            x-init="$nextTick(() => pick({{ $preselect }}))"
        @endif
        class="mx-auto max-w-page px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12">

        <x-breadcrumb :items="['Beranda' => route('home'), 'Cari kost' => route('kost.index'), $property->name => null]" class="mb-5" />

        <x-gallery :images="$property->images" :title="$property->name" />

        <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="min-w-0 space-y-12">
                {{-- Judul --}}
                <header>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge tone="info" :icon="$property->gender_target->icon()" :label="'Kost '.strtolower($property->gender_target->label())" />
                        <x-rating :value="$property->rating_avg" :count="$property->reviews_count" />
                    </div>
                    <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $property->name }}</h1>
                    <p class="mt-2 flex items-start gap-2 text-ink-500">
                        <x-lucide-map-pin class="mt-0.5 h-4 w-4 shrink-0" stroke-width="1.75" aria-hidden="true" />
                        {{ $property->address }}, {{ $property->city }}
                    </p>
                    @if ($property->description)
                        <div class="mt-6 max-w-prose space-y-3 leading-relaxed text-ink-700">
                            @foreach (preg_split('/\n\s*\n/', $property->description) as $para)
                                <p>{{ $para }}</p>
                            @endforeach
                        </div>
                    @endif
                </header>

                {{-- Fasilitas bersama --}}
                @if ($property->facilities->isNotEmpty())
                    <section aria-labelledby="fasilitas">
                        <h2 id="fasilitas" class="font-display text-2xl font-semibold">Fasilitas bersama</h2>
                        <ul class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($property->facilities as $facility)
                                <li class="flex items-center gap-3 rounded-xl border border-kapur-200 bg-white px-3.5 py-3 text-sm font-medium">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-tegel-50 text-tegel-700">
                                        <x-dynamic-component :component="$facility->iconComponent()" class="h-[18px] w-[18px]" stroke-width="1.75" aria-hidden="true" />
                                    </span>
                                    {{ $facility->name }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Daftar kamar --}}
                <section id="kamar" class="scroll-mt-24" aria-labelledby="kamar-judul">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <h2 id="kamar-judul" class="font-display text-2xl font-semibold">Pilih kamar</h2>
                        <p class="text-sm text-ink-500"><span class="num font-semibold text-ink-900">{{ $availableCount }}</span> dari {{ $rooms->count() }} kamar tersedia</p>
                    </div>

                    <ul class="mt-5 space-y-3">
                        @foreach ($rooms as $room)
                            @php $isAvailable = $room->status === RoomStatus::Available; @endphp
                            <li @class([
                                'grid gap-4 rounded-xl border bg-white p-4 sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:items-center',
                                'border-kapur-200' => $isAvailable,
                                'border-kapur-200 bg-kapur-50/60' => ! $isAvailable,
                            ])>
                                <div @class([
                                    'flex h-14 w-14 flex-col items-center justify-center rounded-xl font-display',
                                    'bg-tegel-700 text-white' => $isAvailable,
                                    'bg-kapur-200 text-ink-500' => ! $isAvailable,
                                ])>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider opacity-75">Kamar</span>
                                    <span class="text-lg font-bold leading-none">{{ $room->code }}</span>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-500">
                                        <span>Lantai {{ $room->floor ?? '-' }}</span>
                                        @if ($room->size_m2)
                                            <span class="num">{{ rtrim(rtrim(number_format($room->size_m2, 1, ',', '.'), '0'), ',') }} m²</span>
                                        @endif
                                        <x-status-badge :status="$room->status" size="sm" />
                                    </div>
                                    <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1.5">
                                        @foreach ($room->facilities as $facility)
                                            <li class="flex items-center gap-1.5 text-sm text-ink-700">
                                                <x-dynamic-component :component="$facility->iconComponent()" class="h-4 w-4 text-tegel-600" stroke-width="1.75" aria-hidden="true" />
                                                {{ $facility->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="flex items-center justify-between gap-4 border-t border-kapur-100 pt-3 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                                    <p class="num whitespace-nowrap font-display text-lg font-semibold text-tegel-800"><x-money :amount="$room->monthly_price" /><span class="text-sm font-medium text-ink-500">/bln</span></p>
                                    @if ($isAvailable)
                                        @guest
                                            <x-button :href="route('kost.apply', [$property, 'kamar' => $room->id])" size="sm">Ajukan sewa</x-button>
                                        @else
                                            @if (! $blocker && $canApply)
                                                <x-button size="sm" x-on:click="pick({{ $room->id }})">Ajukan sewa</x-button>
                                            @else
                                                <x-button size="sm" disabled title="{{ $blocker }}">Ajukan sewa</x-button>
                                            @endif
                                        @endguest
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- Aturan --}}
                @if ($ruleLines->isNotEmpty())
                    <section aria-labelledby="aturan">
                        <h2 id="aturan" class="font-display text-2xl font-semibold">Aturan kost</h2>
                        <ul class="mt-5 space-y-3">
                            @foreach ($ruleLines as $line)
                                <li class="flex gap-3 leading-relaxed text-ink-700">
                                    <x-lucide-scroll-text class="mt-1 h-4 w-4 shrink-0 text-kuningan-700" stroke-width="1.75" aria-hidden="true" />
                                    {{ $line }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Lokasi --}}
                @if ($property->has_location)
                    <section aria-labelledby="lokasi">
                        <h2 id="lokasi" class="font-display text-2xl font-semibold">Lokasi</h2>
                        <div class="mt-5 overflow-hidden rounded-xl border border-kapur-200">
                            <div data-map data-lat="{{ $property->latitude }}" data-lng="{{ $property->longitude }}" data-label="{{ $property->name }}"
                                class="h-72 w-full bg-kapur-100" role="region" aria-label="Peta lokasi {{ $property->name }}"></div>
                        </div>
                        <a href="https://www.openstreetmap.org/?mlat={{ $property->latitude }}&mlon={{ $property->longitude }}#map=17/{{ $property->latitude }}/{{ $property->longitude }}"
                            target="_blank" rel="noopener" class="link mt-3 inline-flex items-center gap-1.5 text-sm">
                            Buka di peta besar
                            <x-lucide-external-link class="h-3.5 w-3.5" stroke-width="2" aria-hidden="true" />
                        </a>
                    </section>
                @endif

                {{-- Ulasan --}}
                <section aria-labelledby="ulasan">
                    <h2 id="ulasan" class="font-display text-2xl font-semibold">Ulasan penghuni</h2>
                    @if ($reviews->isEmpty())
                        <div class="card mt-5">
                            <x-empty-state icon="message-square-quote" title="Belum ada ulasan" description="Ulasan muncul setelah penghuni tinggal minimal 30 hari." compact />
                        </div>
                    @else
                        <div class="mt-5 grid gap-6 md:grid-cols-[12rem_minmax(0,1fr)]">
                            <div>
                                <p class="num font-display text-5xl font-semibold">{{ number_format($property->rating_avg, 1, ',', '.') }}</p>
                                <p class="text-sm text-ink-500">dari {{ $property->reviews_count }} ulasan</p>
                                <dl class="mt-4 space-y-1.5">
                                    @for ($star = 5; $star >= 1; $star--)
                                        @php $n = (int) ($ratingBreakdown[$star] ?? 0); @endphp
                                        <div class="flex items-center gap-2 text-xs">
                                            <dt class="num w-3 text-ink-500">{{ $star }}</dt>
                                            <dd class="flex flex-1 items-center gap-2">
                                                <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-kapur-100">
                                                    <span class="block h-full rounded-full bg-kuningan-500" style="width: {{ $property->reviews_count ? round($n / $property->reviews_count * 100) : 0 }}%"></span>
                                                </span>
                                                <span class="num w-4 text-right text-ink-500">{{ $n }}</span>
                                            </dd>
                                        </div>
                                    @endfor
                                </dl>
                            </div>
                            <ul class="space-y-4">
                                @foreach ($reviews as $review)
                                    <li class="rounded-xl border border-kapur-200 bg-white p-5">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <x-avatar :user="$review->user" size="sm" />
                                                <div>
                                                    <p class="text-sm font-semibold">{{ $review->user->name }}</p>
                                                    <p class="text-xs text-ink-500">{{ $review->created_at->locale('id')->translatedFormat('F Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-0.5" aria-label="Rating {{ $review->rating }} dari 5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <x-lucide-star @class(['h-4 w-4', 'fill-kuningan-500 text-kuningan-500' => $i <= $review->rating, 'text-ink-300' => $i > $review->rating]) stroke-width="1.5" aria-hidden="true" />
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="mt-3 leading-relaxed text-ink-700">{{ $review->comment }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            </div>

            {{-- Kartu harga: sticky di desktop --}}
            <aside class="hidden lg:block">
                <div class="sticky top-24 rounded-2xl border border-kapur-200 bg-white p-6 shadow-lift">
                    <p class="text-sm text-ink-500">Mulai dari</p>
                    <p class="num font-display text-3xl font-semibold tracking-tight text-tegel-800"><x-money :amount="$startingPrice" /><span class="text-base font-medium text-ink-500">/bulan</span></p>
                    <div class="mt-4">
                        @if ($availableCount)
                            <x-status-badge tone="success" icon="door-open" :label="'Tersedia '.$availableCount.' kamar'" />
                        @else
                            <x-status-badge tone="neutral" icon="door-closed" label="Semua kamar terisi" />
                        @endif
                    </div>
                    @if ($blocker)
                        <p class="mt-4 rounded-lg bg-warning-soft p-3 text-sm text-warning">{{ $blocker }}</p>
                    @endif
                    <x-button href="#kamar" class="mt-5 w-full" size="lg" :disabled="! $availableCount">Pilih kamar</x-button>
                    <ul class="mt-5 space-y-2 border-t border-kapur-100 pt-5 text-sm text-ink-500">
                        <li class="flex gap-2"><x-lucide-calendar-days class="h-4 w-4 shrink-0 text-tegel-600" stroke-width="1.75" aria-hidden="true" /> Durasi 1, 3, 6, atau 12 bulan</li>
                        <li class="flex gap-2"><x-lucide-receipt-text class="h-4 w-4 shrink-0 text-tegel-600" stroke-width="1.75" aria-hidden="true" /> Bayar per bulan, jatuh tempo {{ config('kost.grace_days') }} hari</li>
                        <li class="flex gap-2"><x-lucide-shield-check class="h-4 w-4 shrink-0 text-tegel-600" stroke-width="1.75" aria-hidden="true" /> Tanpa biaya pendaftaran</li>
                    </ul>
                </div>
            </aside>
        </div>

        {{-- Bottom bar mobile --}}
        <div class="fixed inset-x-0 bottom-0 z-nav border-t border-kapur-200 bg-white/95 px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] backdrop-blur lg:hidden">
            <div class="mx-auto flex max-w-page items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-ink-500">Mulai dari</p>
                    <p class="num font-display text-lg font-semibold text-tegel-800"><x-money :amount="$startingPrice" /><span class="text-sm font-medium text-ink-500">/bln</span></p>
                </div>
                <x-button href="#kamar">Pilih kamar</x-button>
            </div>
        </div>

        {{-- Modal ajukan sewa (P4) --}}
        @auth
            @if ($canApply && ! $blocker)
                <x-modal name="ajukan-sewa" title="Ajukan sewa" :description="$property->name" max-width="lg">
                    <form method="POST" action="{{ route('app.bookings.store') }}" class="space-y-5 p-5 sm:p-6">
                        @csrf
                        <input type="hidden" name="room_id" :value="room?.id">

                        <div class="flex items-center gap-4 rounded-xl bg-tegel-50 p-4">
                            <div class="flex h-12 w-12 flex-col items-center justify-center rounded-lg bg-tegel-700 font-display text-white">
                                <span class="text-sm font-bold" x-text="room?.code"></span>
                            </div>
                            <div class="text-sm">
                                <p class="font-semibold text-ink-900">Kamar <span x-text="room?.code"></span>, lantai <span x-text="room?.floor ?? '-'"></span></p>
                                <p class="num text-ink-500"><span x-text="rupiah(room?.price ?? 0)"></span> per bulan</p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="b-start" class="label">Tanggal mulai <span class="text-danger" aria-hidden="true">*</span></label>
                                <input id="b-start" type="date" name="start_date" x-model="start" required
                                    min="{{ today()->toDateString() }}" max="{{ today()->addDays(60)->toDateString() }}" class="field num">
                                <p class="mt-1.5 text-xs text-ink-500">Paling lambat 60 hari dari hari ini.</p>
                            </div>
                            <fieldset>
                                <legend class="label">Durasi <span class="text-danger" aria-hidden="true">*</span></legend>
                                <div class="grid grid-cols-4 gap-1.5">
                                    @foreach (config('kost.durations') as $months)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="duration_months" value="{{ $months }}" x-model.number="duration" class="peer sr-only" required>
                                            <span class="flex h-10 items-center justify-center rounded-lg border border-kapur-300 text-sm font-semibold transition peer-checked:border-tegel-700 peer-checked:bg-tegel-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-kuningan-500">{{ $months }} bln</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>

                        <dl class="grid grid-cols-2 gap-3 rounded-xl border border-kapur-200 p-4 text-sm">
                            <div>
                                <dt class="text-ink-500">Selesai sewa</dt>
                                <dd class="num mt-0.5 font-semibold" x-text="endDate"></dd>
                            </div>
                            <div>
                                <dt class="text-ink-500">Total sewa</dt>
                                <dd class="num mt-0.5 font-display text-lg font-semibold text-tegel-800" x-text="rupiah(total)"></dd>
                            </div>
                        </dl>

                        <x-textarea name="note" label="Catatan untuk pengelola" rows="2" maxlength="500" hint="Opsional. Misal: rencana jam kedatangan atau pertanyaan soal kamar." />

                        <label class="flex items-start gap-3 text-sm text-ink-700">
                            <input type="checkbox" name="agree_rules" value="1" required class="mt-0.5 h-4 w-4 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                            <span>Saya sudah membaca dan setuju dengan <a href="#aturan" x-on:click="$dispatch('close-modal', 'ajukan-sewa')" class="link">aturan kost</a>.</span>
                        </label>

                        <div class="flex flex-col-reverse gap-2 border-t border-kapur-100 pt-5 sm:flex-row sm:justify-end">
                            <x-button variant="secondary" x-on:click="$dispatch('close-modal', 'ajukan-sewa')">Batal</x-button>
                            <x-button type="submit" icon="send">Kirim pengajuan</x-button>
                        </div>
                    </form>
                </x-modal>
            @endif
        @endauth
    </div>
</x-layouts.public>
