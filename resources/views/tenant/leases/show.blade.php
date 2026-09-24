@php
    $property = $lease->room->property;
    $reviewRoute = Route::has('app.leases.review');
@endphp

<x-layouts.tenant :title="$lease->code" :heading="$property->name.', '.$lease->room->label" :subheading="'Kontrak '.$lease->code">
    <x-slot name="actions">
        <x-button :href="route('app.leases.index')" variant="ghost" icon="arrow-left">Semua kontrak</x-button>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-semibold">Ringkasan</h2>
                <x-status-badge :status="$lease->status" />
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-ink-500">Mulai</dt><dd class="num font-semibold">{{ tanggal($lease->start_date) }}</dd></div>
                <div><dt class="text-ink-500">Selesai</dt><dd class="num font-semibold">{{ tanggal($lease->end_date) }}</dd></div>
                <div><dt class="text-ink-500">Durasi</dt><dd class="num font-semibold">{{ $lease->duration_months }} bulan</dd></div>
                <div><dt class="text-ink-500">Harga/bulan</dt><dd class="num font-semibold"><x-money :amount="$lease->monthly_price" /></dd></div>
                <div><dt class="text-ink-500">Total</dt><dd class="num font-semibold"><x-money :amount="$lease->total_amount" /></dd></div>
                <div><dt class="text-ink-500">Sudah dibayar</dt><dd class="num font-semibold text-tegel-800"><x-money :amount="$lease->paid_amount" /></dd></div>
            </dl>
            @if ($lease->terminated_at)
                <p class="mt-4 rounded-lg bg-danger-soft p-3 text-sm text-danger">Kontrak diakhiri {{ tanggal($lease->terminated_at) }}. Alasan: {{ $lease->termination_reason }}</p>
            @endif
        </section>

        <section class="card p-5">
            <h2 class="font-display text-lg font-semibold">Kamar</h2>
            <p class="mt-2 text-sm text-ink-700">{{ $property->address }}, {{ $property->city }}</p>
            <x-button :href="route('kost.show', $property)" variant="secondary" size="sm" icon="external-link" class="mt-4">Lihat halaman kost</x-button>
        </section>
    </div>

    {{-- Tagihan & pembayaran --}}
    <section class="mt-8">
        <h2 class="mb-3 font-display text-lg font-semibold">Tagihan & pembayaran</h2>
        <x-table>
            <x-slot name="head"><th>Periode</th><th>Jatuh tempo</th><th class="is-money">Nominal</th><th>Status</th><th>Pembayaran</th></x-slot>
            @foreach ($lease->invoices as $invoice)
                @php $last = $invoice->payments->first(); @endphp
                <tr>
                    <td data-label="Periode">
                        <span class="block font-medium text-ink-900">{{ $invoice->periodLabel() }}</span>
                        <span class="num block text-xs text-ink-500">{{ $invoice->number }}</span>
                    </td>
                    <td data-label="Jatuh tempo" class="num whitespace-nowrap">{{ tanggal($invoice->due_date) }}</td>
                    <td data-label="Nominal" class="is-money"><x-money :amount="$invoice->amount" /></td>
                    <td data-label="Status"><x-status-badge :status="$invoice->status" size="sm" /></td>
                    <td data-label="Pembayaran">
                        @if ($last)
                            <span class="block text-sm">{{ $last->method->label() }}, {{ tanggal($last->paid_at) }}</span>
                            <span class="block text-xs text-ink-500">{{ $last->status->label() }}{{ $last->reject_reason ? ': '.$last->reject_reason : '' }}</span>
                        @elseif ($invoice->isPayable())
                            <a href="{{ route('app.invoices.index', ['bayar' => $invoice->id]) }}" class="link text-sm">Bayar</a>
                        @else
                            <span class="text-ink-400">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
    </section>

    {{-- Ulasan (FR-REV) --}}
    <section id="ulasan" class="mt-8 scroll-mt-24">
        <h2 class="mb-3 font-display text-lg font-semibold">Ulasan</h2>
        @if ($lease->review)
            <div class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex gap-0.5" aria-label="Rating {{ $lease->review->rating }} dari 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-lucide-star @class(['h-5 w-5', 'fill-kuningan-500 text-kuningan-500' => $i <= $lease->review->rating, 'text-ink-300' => $i > $lease->review->rating]) stroke-width="1.5" aria-hidden="true" />
                        @endfor
                    </div>
                    @unless ($lease->review->is_published)
                        <x-status-badge tone="neutral" icon="eye-off" label="Disembunyikan pemilik" size="sm" />
                    @endunless
                </div>
                <p class="mt-3 text-ink-700">{{ $lease->review->comment }}</p>
                @if ($reviewRoute && auth()->user()->can('update', $lease->review))
                    <p class="mt-3 text-xs text-ink-500">Kamu masih bisa mengubah ulasan sampai {{ tanggal($lease->review->created_at->copy()->addDays((int) config('kost.review_edit_days'))) }}.</p>
                    @include('tenant.leases.partials.review-form', ['review' => $lease->review])
                @endif
            </div>
        @elseif ($canReview && $reviewRoute)
            <div class="card p-5">
                <p class="text-sm text-ink-500">Bagaimana pengalamanmu tinggal di {{ $property->name }}? Ulasanmu membantu calon penghuni lain.</p>
                @include('tenant.leases.partials.review-form', ['review' => null])
            </div>
        @else
            <div class="card">
                <x-empty-state icon="message-square-quote" title="Belum bisa menulis ulasan" compact
                    :description="'Ulasan bisa ditulis setelah tinggal minimal '.config('kost.review_min_active_days').' hari atau setelah kontrak selesai.'" />
            </div>
        @endif
    </section>
</x-layouts.tenant>
