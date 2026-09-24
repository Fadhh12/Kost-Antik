@php
    $payRoute = Route::has('app.invoices.payments.store');
    $tabItems = [
        'belum' => ['label' => 'Belum dibayar', 'url' => route('app.invoices.index', ['tab' => 'belum']), 'count' => $counts['belum']],
        'menunggu' => ['label' => 'Menunggu verifikasi', 'url' => route('app.invoices.index', ['tab' => 'menunggu']), 'count' => $counts['menunggu']],
        'lunas' => ['label' => 'Lunas', 'url' => route('app.invoices.index', ['tab' => 'lunas']), 'count' => $counts['lunas']],
    ];
    $reopen = $errors->any() && old('invoice_id');
    $preselect = $reopen ? (int) old('invoice_id') : ($payInvoice?->isPayable() ? $payInvoice->id : null);
@endphp

<x-layouts.tenant title="Tagihan" heading="Tagihan" subheading="Bayar lewat transfer lalu unggah buktinya. Pengelola akan memverifikasi.">
    <div x-data="{
            inv: null,
            pay(data) { this.inv = data; $dispatch('open-modal', 'bayar') },
        }"
        @if ($preselect && $payRoute)
            x-init="$nextTick(() => document.querySelector('[data-pay=&quot;{{ $preselect }}&quot;]')?.click())"
        @endif>

        <x-tabs :tabs="$tabItems" :active="$tab" class="mb-6" />

        @if ($invoices->isEmpty())
            <div class="card">
                <x-empty-state :icon="$tab === 'lunas' ? 'badge-check' : 'receipt-text'"
                    :title="match ($tab) { 'belum' => 'Tidak ada tagihan yang perlu dibayar', 'menunggu' => 'Tidak ada pembayaran yang menunggu', default => 'Belum ada tagihan lunas' }"
                    :description="$tab === 'belum' ? 'Tagihan baru terbit otomatis saat kontrak sewamu aktif.' : null" />
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($invoices as $invoice)
                    @php
                        $last = $invoice->payments->first();
                        $data = [
                            'id' => $invoice->id,
                            'number' => $invoice->number,
                            'period' => $invoice->periodLabel(),
                            'amount' => $invoice->amount,
                            'amountLabel' => rupiah($invoice->amount),
                            'room' => $invoice->lease->room->property->name.', '.$invoice->lease->room->label,
                            'action' => $payRoute ? route('app.invoices.payments.store', $invoice) : '#',
                        ];
                    @endphp
                    <li class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                        <div @class([
                            'flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl font-display',
                            'bg-danger-soft text-danger' => $invoice->status === \App\Enums\InvoiceStatus::Overdue,
                            'bg-tegel-50 text-tegel-800' => $invoice->status !== \App\Enums\InvoiceStatus::Overdue,
                        ])>
                            <span class="text-[10px] font-semibold uppercase">{{ $invoice->period_start->locale('id')->translatedFormat('M') }}</span>
                            <span class="text-sm font-bold leading-none">{{ $invoice->period_start->format('y') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-ink-900">{{ $invoice->periodLabel() }}</p>
                                <x-status-badge :status="$invoice->status" size="sm" />
                            </div>
                            <p class="num mt-0.5 text-sm text-ink-500">{{ $invoice->number }} · {{ $invoice->lease->room->property->name }}, {{ $invoice->lease->room->code }}</p>
                            <p class="num mt-0.5 text-sm text-ink-500">
                                @if ($invoice->paid_at)
                                    Lunas {{ tanggal($invoice->paid_at) }}
                                @else
                                    Jatuh tempo {{ tanggal($invoice->due_date) }}
                                @endif
                            </p>
                            @if ($last?->status === \App\Enums\PaymentStatus::Rejected && $invoice->isPayable())
                                <p class="mt-2 flex gap-2 rounded-lg bg-danger-soft p-2.5 text-sm text-danger">
                                    <x-lucide-circle-alert class="h-4 w-4 shrink-0" stroke-width="2" aria-hidden="true" />
                                    Bukti sebelumnya ditolak: {{ $last->reject_reason }}
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-kapur-100 pt-3 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                            <p class="num font-display text-lg font-semibold"><x-money :amount="$invoice->amount" /></p>
                            @if ($invoice->isPayable() && $payRoute)
                                <x-button size="sm" icon="upload" data-pay="{{ $invoice->id }}" x-on:click="pay(@js($data))">Bayar</x-button>
                            @elseif ($last?->hasProof() && Route::has('payments.proof'))
                                <a href="{{ route('payments.proof', $last) }}" target="_blank" class="link text-sm">Lihat bukti</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <x-pagination :paginator="$invoices" />
        @endif

        {{-- Modal upload bukti (T3) --}}
        @if ($payRoute)
            <x-modal name="bayar" title="Unggah bukti bayar" max-width="lg" :show="(bool) $reopen">
                <form method="POST" :action="inv?.action" enctype="multipart/form-data" class="space-y-5 p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="invoice_id" :value="inv?.id">
                    <input type="hidden" name="amount" :value="inv?.amount">
                    <input type="hidden" name="method" value="transfer">

                    <div class="rounded-xl bg-tegel-800 p-4 text-tegel-100">
                        <p class="text-xs">Transfer ke</p>
                        <p class="mt-0.5 font-display text-lg font-semibold text-white">{{ config('kost.bank_info') }}</p>
                        <p class="mt-2 text-xs">Tulis <span class="num font-semibold text-kuningan-300" x-text="inv?.number"></span> di berita transfer.</p>
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-ink-500">Tagihan</dt><dd class="font-medium" x-text="inv?.period"></dd></div>
                        <div>
                            <dt class="text-ink-500">Nominal (tidak bisa diubah)</dt>
                            <dd class="num font-display text-lg font-semibold text-tegel-800" x-text="inv?.amountLabel"></dd>
                        </div>
                    </dl>

                    <x-input name="paid_at" type="date" label="Tanggal transfer" :value="today()->toDateString()" max="{{ today()->toDateString() }}" required />
                    <x-file-upload name="proof" label="Bukti transfer" accept="image/jpeg,image/png,image/webp,application/pdf" required
                        hint="Foto/tangkapan layar atau PDF, maksimal 3 MB." />

                    <div class="flex flex-col-reverse gap-2 border-t border-kapur-100 pt-5 sm:flex-row sm:justify-end">
                        <x-button variant="secondary" x-on:click="$dispatch('close-modal', 'bayar')">Batal</x-button>
                        <x-button type="submit" icon="send">Kirim bukti</x-button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-layouts.tenant>
