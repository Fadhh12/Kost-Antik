@php
    $user = auth()->user();
    $delta = $revenueLastMonth > 0 ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100) : null;
    $actionCount = $pendingBookings->count() + $pendingPayments->count() + $pendingUsers->count();
@endphp

<x-layouts.admin heading="Dashboard" :subheading="($user->isOwner() ? 'Ringkasan semua gedung' : 'Ringkasan gedung yang kamu kelola').' per '.tanggal(today(), 'l, d F Y').'.'">
    @push('head')
        @vite('resources/js/charts.js')
    @endpush

    {{-- Metrik utama --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Okupansi" :value="number_format($occupancy, 1, ',', '.').'%'" icon="bed-double"
            :hint="$occupied.' dari '.$totalRooms.' kamar terisi di '.$properties->count().' gedung'" />
        <x-stat-card label="Pendapatan bulan ini" :value="rupiah($revenueThisMonth)" icon="wallet" :href="route('admin.payments.index', ['status' => 'verified'])">
            @if ($delta !== null)
                <span @class(['font-semibold', 'text-success' => $delta >= 0, 'text-danger' => $delta < 0])>{{ $delta >= 0 ? '+' : '' }}{{ $delta }}%</span>
                dibanding bulan lalu ({{ rupiah($revenueLastMonth) }})
            @else
                Dari pembayaran terverifikasi
            @endif
        </x-stat-card>
        <x-stat-card label="Tagihan terlambat" :value="$overdueCount" icon="clock-alert" :tone="$overdueCount ? 'danger' : null"
            :hint="$overdueCount ? 'Total '.rupiah($overdueAmount) : 'Semua tagihan tertib'" :href="route('admin.invoices.index', ['status' => 'overdue'])" />
        <x-stat-card label="Perlu tindakan" :value="$actionCount" icon="list-todo" :tone="$actionCount ? 'warning' : null"
            hint="Booking, pembayaran, dan akun yang menunggu" />
    </div>

    <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        {{-- Grafik pendapatan --}}
        <section class="card p-5" aria-labelledby="grafik-pendapatan">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="grafik-pendapatan" class="font-display text-base font-semibold">Pendapatan 6 bulan terakhir</h2>
                <p class="num text-sm text-ink-500">Total {{ rupiah(array_sum($revenue['values'])) }}</p>
            </div>
            <div class="relative mt-4 h-64">
                <canvas data-revenue-chart data-url="{{ route('admin.dashboard.revenue') }}" role="img" aria-label="Grafik pendapatan per bulan"></canvas>
                <p data-chart-status class="absolute inset-0 flex items-center justify-center text-sm text-ink-500">Memuat grafik...</p>
            </div>
            <details class="mt-3 text-sm">
                <summary class="cursor-pointer font-medium text-tegel-700">Lihat sebagai tabel</summary>
                <table class="mt-2 w-full">
                    <thead><tr class="text-left text-xs text-ink-500"><th class="py-1 font-medium">Bulan</th><th class="py-1 text-right font-medium">Pendapatan</th></tr></thead>
                    <tbody class="divide-y divide-kapur-100">
                        @foreach ($revenue['labels'] as $i => $label)
                            <tr><td class="py-1.5">{{ $label }}</td><td class="num py-1.5 text-right">{{ rupiah($revenue['values'][$i]) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        </section>

        {{-- Perlu tindakan --}}
        <section class="card flex flex-col" aria-labelledby="tindakan">
            <h2 id="tindakan" class="border-b border-kapur-100 px-5 py-4 font-display text-base font-semibold">Perlu tindakan</h2>
            @if ($actionCount === 0)
                <x-empty-state icon="badge-check" title="Semua beres" description="Tidak ada booking, pembayaran, atau akun yang menunggu." compact class="flex-1" />
            @else
                <ul class="divide-y divide-kapur-100 text-sm">
                    @foreach ($pendingPayments as $payment)
                        <li>
                            <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-kapur-50">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-soft text-warning"><x-lucide-wallet class="h-4 w-4" stroke-width="1.75" aria-hidden="true" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-ink-900">Verifikasi {{ rupiah($payment->amount) }}</span>
                                    <span class="block truncate text-xs text-ink-500">{{ $payment->invoice->lease->user->name }} · {{ $payment->invoice->lease->room->property->name }}</span>
                                </span>
                                <x-lucide-chevron-right class="h-4 w-4 text-ink-300" stroke-width="2" aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                    @foreach ($pendingBookings as $booking)
                        <li>
                            <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-kapur-50">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-tegel-50 text-tegel-700"><x-lucide-calendar-check class="h-4 w-4" stroke-width="1.75" aria-hidden="true" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-ink-900">Booking {{ $booking->room->label }}</span>
                                    <span class="block truncate text-xs text-ink-500">{{ $booking->user->name }} · {{ $booking->room->property->name }}</span>
                                </span>
                                <x-lucide-chevron-right class="h-4 w-4 text-ink-300" stroke-width="2" aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                    @foreach ($pendingUsers as $pending)
                        <li>
                            <a href="{{ route('admin.users.index', ['tab' => 'pending']) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-kapur-50">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-kapur-100 text-ink-700"><x-lucide-user-round-check class="h-4 w-4" stroke-width="1.75" aria-hidden="true" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-ink-900">Verifikasi akun {{ $pending->name }}</span>
                                    <span class="block truncate text-xs text-ink-500">Daftar {{ $pending->created_at->locale('id')->diffForHumans() }}</span>
                                </span>
                                <x-lucide-chevron-right class="h-4 w-4 text-ink-300" stroke-width="2" aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        {{-- Okupansi per gedung --}}
        <section class="card p-5" aria-labelledby="okupansi-gedung">
            <h2 id="okupansi-gedung" class="font-display text-base font-semibold">Okupansi per gedung</h2>
            @if ($properties->isEmpty())
                <x-empty-state icon="building-2" title="Belum ada gedung" compact />
            @else
                <ul class="mt-4 space-y-4">
                    @foreach ($properties as $property)
                        @php
                            $bookable = $property->rooms_count - $property->maintenance_rooms_count;
                            $pct = $bookable > 0 ? round($property->occupied_rooms_count / $bookable * 100) : 0;
                        @endphp
                        <li>
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <a href="{{ route('admin.properties.show', $property) }}" class="truncate font-medium text-ink-900 hover:text-tegel-700">{{ $property->name }}</a>
                                <span class="num shrink-0 text-ink-500"><span class="font-semibold text-ink-900">{{ $pct }}%</span> · {{ $property->occupied_rooms_count }}/{{ $bookable }} kamar</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-kapur-100" role="img" aria-label="{{ $property->name }} terisi {{ $pct }} persen">
                                <div class="h-full rounded-full bg-tegel-600" style="width: {{ $pct }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Kontrak segera berakhir --}}
        <section class="card flex flex-col" aria-labelledby="segera-berakhir">
            <h2 id="segera-berakhir" class="border-b border-kapur-100 px-5 py-4 font-display text-base font-semibold">Kontrak berakhir dalam 30 hari</h2>
            @if ($expiringLeases->isEmpty())
                <x-empty-state icon="calendar-clock" title="Tidak ada" description="Belum ada kontrak yang akan berakhir bulan ini." compact class="flex-1" />
            @else
                <ul class="divide-y divide-kapur-100 text-sm">
                    @foreach ($expiringLeases as $lease)
                        <li>
                            <a href="{{ route('admin.leases.show', $lease) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-kapur-50">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-ink-900">{{ $lease->user->name }}</span>
                                    <span class="block truncate text-xs text-ink-500">{{ $lease->room->property->name }}, {{ $lease->room->label }}</span>
                                </span>
                                <span class="num shrink-0 text-right">
                                    <span class="block font-medium">{{ tanggal($lease->end_date) }}</span>
                                    <span class="block text-xs text-ink-500">{{ (int) today()->diffInDays($lease->end_date) }} hari lagi</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.admin>
