@php
    use App\Enums\InvoiceStatus;

    $cashRoute = Route::has('admin.invoices.cash');
    $cards = [
        [InvoiceStatus::Overdue, 'danger'],
        [InvoiceStatus::PendingVerification, 'warning'],
        [InvoiceStatus::Unpaid, null],
        [InvoiceStatus::Paid, 'success'],
    ];
@endphp

<x-layouts.admin heading="Tagihan" subheading="Semua tagihan bulanan dari kontrak sewa." :breadcrumb="['Tagihan' => null]">
    {{-- Ringkasan per status (klik untuk menyaring) --}}
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as [$s, $tone])
            @php $row = $summary[$s->value] ?? null; @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => $status === $s ? null : $s->value, 'page' => null]) }}"
                @class(['card flex items-center justify-between gap-3 p-4 transition hover:border-tegel-300', 'ring-2 ring-tegel-600' => $status === $s])>
                <div>
                    <p class="text-sm text-ink-500">{{ $s->label() }}</p>
                    <p @class(['num font-display text-xl font-semibold', 'text-danger' => $tone === 'danger' && $row, 'text-warning' => $tone === 'warning' && $row])>{{ (int) ($row->total ?? 0) }}</p>
                </div>
                <p class="num text-right text-sm font-medium text-ink-700">{{ rupiah($row->amount ?? 0) }}</p>
            </a>
        @endforeach
    </div>

    <form method="GET" data-no-lock class="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_12rem_14rem_10rem]">
        @if ($status) <input type="hidden" name="status" value="{{ $status->value }}"> @endif
        <div class="relative">
            <label for="q" class="sr-only">Cari tagihan</label>
            <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" stroke-width="2" aria-hidden="true" />
            <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Nomor tagihan atau nama penyewa" class="field pl-9">
        </div>
        <label for="periode" class="sr-only">Periode</label>
        <input id="periode" type="month" name="periode" value="{{ request('periode') }}" class="field num" onchange="this.form.submit()">
        <label for="property" class="sr-only">Gedung</label>
        <select id="property" name="property" class="field" onchange="this.form.submit()">
            <option value="">Semua gedung</option>
            @foreach ($properties as $id => $name)
                <option value="{{ $id }}" @selected((int) request('property') === $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-button :href="route('admin.invoices.index')" variant="ghost" icon="rotate-ccw">Reset</x-button>
    </form>

    @if ($invoices->isEmpty())
        <div class="card"><x-empty-state icon="receipt-text" title="Tidak ada tagihan" description="Coba ubah filter status, periode, atau gedung." /></div>
    @else
        <x-table>
            <x-slot name="head">
                <th>Tagihan</th><th>Penyewa</th><th>Periode</th><th>Jatuh tempo</th><th class="is-money">Nominal</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
            </x-slot>
            @foreach ($invoices as $invoice)
                <tr>
                    <td data-label="Tagihan">
                        <a href="{{ route('admin.leases.show', $invoice->lease) }}" class="num block whitespace-nowrap font-medium text-ink-900 hover:text-tegel-700">{{ $invoice->number }}</a>
                        <span class="block text-xs text-ink-500">Bulan ke-{{ $invoice->sequence }}</span>
                    </td>
                    <td data-label="Penyewa">
                        <span class="block">{{ $invoice->lease->user->name }}</span>
                        <span class="block text-xs text-ink-500">{{ $invoice->lease->room->property->name }}, {{ $invoice->lease->room->code }}</span>
                    </td>
                    <td data-label="Periode" class="whitespace-nowrap">{{ $invoice->periodLabel() }}</td>
                    <td data-label="Jatuh tempo" class="num whitespace-nowrap">
                        {{ tanggal($invoice->due_date) }}
                        @if ($invoice->status === InvoiceStatus::Overdue)
                            <span class="block text-xs font-semibold text-danger">Terlambat {{ (int) $invoice->due_date->diffInDays(today()) }} hari</span>
                        @endif
                    </td>
                    <td data-label="Nominal" class="is-money"><x-money :amount="$invoice->amount" /></td>
                    <td data-label="Status"><x-status-badge :status="$invoice->status" size="sm" /></td>
                    <td class="is-actions">
                        @if ($invoice->pendingPayment && Route::has('admin.payments.index'))
                            <x-button :href="route('admin.payments.index', ['status' => 'pending'])" size="sm">Verifikasi</x-button>
                        @elseif ($cashRoute && $invoice->isPayable() && auth()->user()->can('recordCash', $invoice))
                            <x-button size="sm" variant="secondary" icon="banknote" x-data x-on:click="$dispatch('open-modal', 'tunai-{{ $invoice->id }}')">Catat tunai</x-button>
                            <x-confirm-modal :name="'tunai-'.$invoice->id" title="Catat pembayaran tunai?" :action="route('admin.invoices.cash', $invoice)" method="POST" variant="primary" confirm="Catat lunas" icon="banknote">
                                Tagihan {{ $invoice->number }} milik {{ $invoice->lease->user->name }} sebesar {{ rupiah($invoice->amount) }} akan langsung ditandai lunas.
                                <x-slot name="fields">
                                    <x-input name="paid_at" type="date" label="Tanggal bayar" :value="today()->toDateString()" :id="'paid-at-'.$invoice->id" max="{{ today()->toDateString() }}" required />
                                </x-slot>
                            </x-confirm-modal>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$invoices" />
    @endif
</x-layouts.admin>
