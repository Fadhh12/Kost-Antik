<x-layouts.admin heading="Laporan pembayaran" subheading="Pembayaran terverifikasi berdasarkan tanggal verifikasi." :breadcrumb="['Laporan' => null]">
    <x-slot name="actions">
        <x-button :href="request()->fullUrlWithQuery(['format' => 'csv', 'page' => null])" variant="secondary" icon="download">Ekspor CSV</x-button>
    </x-slot>

    <form method="GET" data-no-lock class="card mb-6 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[repeat(3,minmax(0,1fr))_auto] lg:items-end">
        <x-input name="dari" type="date" label="Dari" :value="$from->toDateString()" />
        <x-input name="sampai" type="date" label="Sampai" :value="$to->toDateString()" />
        <x-select name="property" label="Gedung" :options="$properties" :value="request('property')" placeholder="Semua gedung" />
        <x-button type="submit" icon="filter">Terapkan</x-button>
    </form>

    <div class="mb-6 grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
        <x-stat-card label="Total pendapatan" :value="rupiah($total)" icon="wallet" :hint="$count.' pembayaran, '.tanggal($from).' - '.tanggal($to)" />
        <section class="card p-5">
            <h2 class="text-sm font-medium text-ink-500">Per gedung</h2>
            @if ($byProperty->isEmpty())
                <p class="mt-3 text-sm text-ink-500">Tidak ada pembayaran pada periode ini.</p>
            @else
                <ul class="mt-3 divide-y divide-kapur-100 text-sm">
                    @foreach ($byProperty as $name => $row)
                        <li class="flex justify-between gap-3 py-2">
                            <span>{{ $name }} <span class="text-ink-500">({{ $row['count'] }})</span></span>
                            <span class="num font-medium">{{ rupiah($row['amount']) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    @if ($payments->isEmpty())
        <div class="card"><x-empty-state icon="chart-column" title="Belum ada data" description="Coba perluas rentang tanggal." /></div>
    @else
        <x-table>
            <x-slot name="head"><th>Diverifikasi</th><th>Tagihan</th><th>Penyewa</th><th>Metode</th><th class="is-money">Nominal</th></x-slot>
            @foreach ($payments as $payment)
                <tr>
                    <td data-label="Diverifikasi" class="num whitespace-nowrap">{{ tanggal($payment->verified_at) }}</td>
                    <td data-label="Tagihan"><span class="num block whitespace-nowrap">{{ $payment->invoice->number }}</span><span class="block text-xs text-ink-500">{{ $payment->invoice->periodLabel() }}</span></td>
                    <td data-label="Penyewa"><span class="block">{{ $payment->invoice->lease->user->name }}</span><span class="block text-xs text-ink-500">{{ $payment->invoice->lease->room->property->name }}, {{ $payment->invoice->lease->room->code }}</span></td>
                    <td data-label="Metode">{{ $payment->method->label() }}</td>
                    <td data-label="Nominal" class="is-money font-medium text-ink-900"><x-money :amount="$payment->amount" /></td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$payments" />
    @endif
</x-layouts.admin>
