@php
    $canTerminate = $lease->isActive() && auth()->user()->can('terminate', $lease);
    $cashRoute = Route::has('admin.invoices.cash');
@endphp

<x-layouts.admin :title="$lease->code" :heading="'Kontrak '.$lease->code" :subheading="$lease->room->property->name.', '.$lease->room->label"
    :breadcrumb="['Kontrak' => route('admin.leases.index'), $lease->code => null]">
    @if ($canTerminate)
        <x-slot name="actions">
            <x-button variant="secondary" icon="octagon-x" class="text-danger" x-data x-on:click="$dispatch('open-modal', 'akhiri-kontrak')">Akhiri kontrak</x-button>
        </x-slot>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            {{-- Ringkasan --}}
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card label="Status" :value="$lease->status->label()" :tone="$lease->isActive() ? 'success' : null">
                    @if ($lease->hasArrears()) <span class="font-semibold text-danger">Ada tunggakan</span> @elseif ($lease->isActive()) Sisa {{ $lease->remaining_days }} hari @endif
                </x-stat-card>
                <x-stat-card label="Harga/bulan" :value="rupiah($lease->monthly_price)" hint="Harga saat kontrak dibuat" />
                <x-stat-card label="Total kontrak" :value="rupiah($lease->total_amount)" :hint="$lease->duration_months.' bulan'" />
                <x-stat-card label="Sudah dibayar" :value="rupiah($lease->paid_amount)" :hint="$lease->payment_progress.'% dari total'" />
            </section>

            {{-- Jadwal tagihan --}}
            <section>
                <h2 class="mb-3 font-display text-lg font-semibold">Jadwal tagihan</h2>
                <x-table>
                    <x-slot name="head">
                        <th>#</th><th>Nomor</th><th>Periode</th><th>Jatuh tempo</th><th class="is-money">Nominal</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
                    </x-slot>
                    @foreach ($lease->invoices as $invoice)
                        <tr>
                            <td data-label="Bulan ke" class="num">{{ $invoice->sequence }}</td>
                            <td data-label="Nomor" class="num font-medium text-ink-900">{{ $invoice->number }}</td>
                            <td data-label="Periode" class="num whitespace-nowrap">{{ tanggal($invoice->period_start) }} - {{ tanggal($invoice->period_end) }}</td>
                            <td data-label="Jatuh tempo" class="num whitespace-nowrap">{{ tanggal($invoice->due_date) }}</td>
                            <td data-label="Nominal" class="is-money"><x-money :amount="$invoice->amount" /></td>
                            <td data-label="Status">
                                <x-status-badge :status="$invoice->status" size="sm" />
                                @if ($invoice->paid_at)
                                    <span class="mt-0.5 block text-xs text-ink-500">{{ tanggal($invoice->paid_at) }}</span>
                                @endif
                            </td>
                            <td class="is-actions">
                                @if ($cashRoute && $invoice->isPayable() && auth()->user()->can('recordCash', $invoice))
                                    <x-button size="sm" variant="secondary" icon="banknote" x-data x-on:click="$dispatch('open-modal', 'tunai-{{ $invoice->id }}')">Catat tunai</x-button>
                                    <x-confirm-modal :name="'tunai-'.$invoice->id" title="Catat pembayaran tunai?" :action="route('admin.invoices.cash', $invoice)" method="POST" variant="primary" confirm="Catat lunas" icon="banknote">
                                        Tagihan {{ $invoice->number }} sebesar {{ rupiah($invoice->amount) }} akan langsung ditandai lunas.
                                        <x-slot name="fields">
                                            <x-input name="paid_at" type="date" label="Tanggal bayar" :value="today()->toDateString()" :id="'paid-at-'.$invoice->id" max="{{ today()->toDateString() }}" required />
                                        </x-slot>
                                    </x-confirm-modal>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </section>

            {{-- Riwayat --}}
            <section>
                <h2 class="mb-3 font-display text-lg font-semibold">Riwayat</h2>
                <ol class="card divide-y divide-kapur-100">
                    @forelse ($history as $entry)
                        <li class="flex items-start gap-3 px-5 py-3 text-sm">
                            <x-lucide-history class="mt-0.5 h-4 w-4 shrink-0 text-ink-400" stroke-width="1.75" aria-hidden="true" />
                            <div>
                                <p class="font-medium text-ink-900">{{ $entry->description }}</p>
                                <p class="text-xs text-ink-500">
                                    {{ $entry->created_at->locale('id')->translatedFormat('d M Y, H.i') }}
                                    · {{ $entry->causer?->name ?? 'Sistem' }}
                                    @if ($entry->properties->get('reason')) · Alasan: {{ $entry->properties->get('reason') }} @endif
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-sm text-ink-500">Belum ada riwayat.</li>
                    @endforelse
                </ol>
            </section>
        </div>

        {{-- Sisi kanan --}}
        <aside class="space-y-6">
            <section class="card p-5">
                <h2 class="text-xs font-semibold text-ink-500">Penyewa</h2>
                <div class="mt-3 flex items-center gap-3">
                    <x-avatar :user="$lease->user" />
                    <div class="min-w-0">
                        <a href="{{ route('admin.users.show', $lease->user) }}" class="font-semibold text-ink-900 hover:text-tegel-700">{{ $lease->user->name }}</a>
                        <p class="truncate text-sm text-ink-500">{{ $lease->user->instance?->name ?? $lease->user->email }}</p>
                    </div>
                </div>
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $lease->user->phone)) }}" target="_blank" rel="noopener" class="link mt-3 inline-flex items-center gap-1.5 text-sm">
                    <x-lucide-message-circle class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />{{ $lease->user->phone }}
                </a>
            </section>

            <section class="card p-5">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Mulai</dt><dd class="num font-medium">{{ tanggal($lease->start_date) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Selesai</dt><dd class="num font-medium">{{ tanggal($lease->end_date) }}</dd></div>
                    @if ($lease->terminated_at)
                        <div class="flex justify-between gap-3"><dt class="text-ink-500">Diakhiri</dt><dd class="num font-medium text-danger">{{ tanggal($lease->terminated_at) }}</dd></div>
                        <div><dt class="text-ink-500">Alasan</dt><dd class="mt-0.5">{{ $lease->termination_reason }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Sumber</dt><dd class="font-medium">{{ $lease->booking ? 'Pengajuan online' : 'Walk-in' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Dibuat oleh</dt><dd class="font-medium">{{ $lease->creator?->name }}</dd></div>
                </dl>
            </section>

            @if ($lease->review)
                <section class="card p-5">
                    <h2 class="text-xs font-semibold text-ink-500">Ulasan penyewa</h2>
                    <p class="mt-2 flex items-center gap-1 text-sm font-semibold"><x-lucide-star class="h-4 w-4 fill-kuningan-500 text-kuningan-500" stroke-width="1.5" aria-hidden="true" />{{ $lease->review->rating }}/5</p>
                    <p class="mt-1 text-sm text-ink-700">{{ $lease->review->comment }}</p>
                </section>
            @endif
        </aside>
    </div>

    @if ($canTerminate)
        <x-confirm-modal name="akhiri-kontrak" title="Akhiri kontrak lebih awal?" :action="route('admin.leases.terminate', $lease)" confirm="Akhiri kontrak" :reason="true" reason-label="Alasan pengakhiran">
            Kamar {{ $lease->room->code }} akan kembali tersedia. Tagihan yang belum dibayar dengan periode setelah tanggal berakhir akan dibatalkan. Tagihan yang sudah lewat tetap tercatat.
            <x-slot name="fields">
                <x-input name="terminated_at" type="date" label="Tanggal berakhir" :value="today()->min($lease->end_date)->toDateString()"
                    min="{{ $lease->start_date->toDateString() }}" max="{{ $lease->end_date->toDateString() }}" required />
            </x-slot>
        </x-confirm-modal>
    @endif
</x-layouts.admin>
