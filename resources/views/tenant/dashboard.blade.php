@php
    $firstName = \Illuminate\Support\Str::of($user->name)->before(' ');
@endphp

<x-layouts.tenant title="Beranda" :heading="'Halo, '.$firstName" subheading="Ringkasan sewa dan tagihanmu hari ini.">
    <x-slot name="banner"><x-account-banner :user="$user" /></x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Kontrak aktif --}}
        <section class="lg:col-span-2" aria-labelledby="kontrak-aktif">
            @if ($activeLease)
                @php $room = $activeLease->room; $property = $room->property; @endphp
                <div class="card overflow-hidden">
                    <div class="grid sm:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                        <div class="relative aspect-[16/10] sm:aspect-auto">
                            <img src="{{ $property->cover_url }}" alt="Foto {{ $property->name }}" class="absolute inset-0 h-full w-full object-cover">
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-ink-500" id="kontrak-aktif">Kontrak aktif</p>
                                    <h2 class="mt-1 font-display text-xl font-semibold">{{ $property->name }}</h2>
                                    <p class="text-sm text-ink-500">{{ $room->label }} · Lantai {{ $room->floor ?? '-' }}</p>
                                </div>
                                <x-status-badge :status="$activeLease->status" size="sm" />
                            </div>

                            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-ink-500">Periode</dt>
                                    <dd class="num mt-0.5 font-semibold">{{ tanggal($activeLease->start_date) }} - {{ tanggal($activeLease->end_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-ink-500">Sisa masa sewa</dt>
                                    <dd class="num mt-0.5 font-semibold">{{ $activeLease->remaining_days }} hari</dd>
                                </div>
                            </dl>

                            <div class="mt-5">
                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="text-ink-500">Sudah dibayar</span>
                                    <span class="num font-semibold"><x-money :amount="$activeLease->paid_amount" /> <span class="font-normal text-ink-500">dari <x-money :amount="$activeLease->total_amount" /></span></span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-kapur-100" role="progressbar" aria-valuenow="{{ $activeLease->payment_progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progres pembayaran">
                                    <div class="h-full rounded-full bg-tegel-600" style="width: {{ $activeLease->payment_progress }}%"></div>
                                </div>
                            </div>

                            @if (Route::has('app.leases.show'))
                                <div class="mt-5">
                                    <x-button :href="route('app.leases.show', $activeLease)" variant="secondary" size="sm" icon-right="arrow-right">Detail kontrak</x-button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif ($lastLease)
                {{-- Flow 4: kontrak selesai -> ajak ulasan & sewa lagi. --}}
                <div class="card p-6">
                    <p class="text-sm font-medium text-ink-500" id="kontrak-aktif">Kontrak terakhir</p>
                    <h2 class="mt-1 font-display text-xl font-semibold">{{ $lastLease->room->property->name }}, {{ $lastLease->room->label }}</h2>
                    <p class="mt-1 text-sm text-ink-500">Berakhir {{ tanggal($lastLease->terminated_at ?? $lastLease->end_date) }}. Terima kasih sudah tinggal di Kost Antik.</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @if (! $lastLease->review && Route::has('app.leases.show'))
                            <x-button :href="route('app.leases.show', $lastLease).'#ulasan'" icon="star">Tulis ulasan</x-button>
                        @endif
                        @if (Route::has('kost.show'))
                            <x-button :href="route('kost.show', $lastLease->room->property)" variant="secondary" icon="rotate-ccw">Sewa lagi</x-button>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <x-empty-state icon="door-open" title="Kamu belum punya kontrak sewa"
                        description="Pilih kamar yang masih kosong, ajukan sewa, lalu pengelola akan memproses pengajuanmu.">
                        @if (Route::has('kost.index'))
                            <x-button :href="route('kost.index')" icon="search">Cari kost</x-button>
                        @endif
                    </x-empty-state>
                </div>
            @endif
        </section>

        {{-- Tagihan berikutnya + rekening --}}
        <aside class="space-y-6">
            <section class="card p-5" aria-labelledby="tagihan-berikut">
                <div class="flex items-center justify-between">
                    <h2 id="tagihan-berikut" class="font-display text-base font-semibold">Tagihan berikutnya</h2>
                    @if ($overdueCount)
                        <x-status-badge tone="danger" icon="clock-alert" :label="$overdueCount.' terlambat'" size="sm" />
                    @endif
                </div>

                @if ($nextInvoice)
                    <p class="num mt-4 font-display text-3xl font-semibold tracking-tight"><x-money :amount="$nextInvoice->amount" /></p>
                    <p class="mt-1 text-sm text-ink-500">{{ $nextInvoice->periodLabel() }} · jatuh tempo <span class="num font-medium text-ink-700">{{ tanggal($nextInvoice->due_date) }}</span></p>
                    <div class="mt-3"><x-status-badge :status="$nextInvoice->status" size="sm" /></div>
                    @if ($nextInvoice->isPayable() && Route::has('app.invoices.index'))
                        <x-button :href="route('app.invoices.index', ['bayar' => $nextInvoice->id])" class="mt-5 w-full" icon="upload">Bayar sekarang</x-button>
                    @endif
                @else
                    <p class="mt-4 text-sm text-ink-500">Tidak ada tagihan yang perlu dibayar.</p>
                @endif
            </section>

            <section class="relative overflow-hidden rounded-xl bg-tegel-800 p-5 text-tegel-100" aria-labelledby="rekening">
                <div class="bg-tegel pointer-events-none absolute inset-0 opacity-[0.08]" aria-hidden="true"></div>
                <div class="relative">
                    <h2 id="rekening" class="flex items-center gap-2 text-sm font-semibold text-white">
                        <x-lucide-landmark class="h-4 w-4 text-kuningan-300" stroke-width="1.75" aria-hidden="true" />
                        Rekening pembayaran
                    </h2>
                    <p class="mt-3 font-display text-lg font-semibold text-white">{{ config('kost.bank_info') }}</p>
                    <p class="mt-1 text-xs text-tegel-200">Tulis nomor tagihan di berita transfer agar verifikasi lebih cepat.</p>
                </div>
            </section>
        </aside>
    </div>

    {{-- Booking terakhir --}}
    @if ($latestBooking)
        <section class="mt-8" aria-labelledby="booking-terakhir">
            <div class="mb-3 flex items-center justify-between">
                <h2 id="booking-terakhir" class="font-display text-lg font-semibold">Pengajuan sewa terakhir</h2>
                @if (Route::has('app.bookings.index'))
                    <a href="{{ route('app.bookings.index') }}" class="link text-sm">Semua pengajuan</a>
                @endif
            </div>
            <div class="card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold">{{ $latestBooking->room->property->name }}, {{ $latestBooking->room->label }}</p>
                    <p class="num mt-0.5 text-sm text-ink-500">Mulai {{ tanggal($latestBooking->start_date) }} · {{ $latestBooking->duration_months }} bulan · diajukan {{ $latestBooking->created_at->locale('id')->diffForHumans() }}</p>
                    @if ($latestBooking->reject_reason)
                        <p class="mt-2 text-sm text-danger">Alasan: {{ $latestBooking->reject_reason }}</p>
                    @endif
                </div>
                <x-status-badge :status="$latestBooking->status" />
            </div>
        </section>
    @endif
</x-layouts.tenant>
