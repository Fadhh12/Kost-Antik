@php
    use App\Enums\PaymentStatus;

    $tabs = collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => [
        'label' => $s === PaymentStatus::Pending ? 'Menunggu' : $s->label(),
        'url' => route('admin.payments.index', array_filter(['status' => $s->value, 'property' => request('property')])),
        'count' => (int) ($counts[$s->value] ?? 0),
    ]])->all();
    $isOwner = auth()->user()->isOwner();
@endphp

<x-layouts.admin heading="Pembayaran" subheading="Cek bukti transfer, lalu verifikasi atau tolak dengan alasan." :breadcrumb="['Pembayaran' => null]">
    <x-slot name="actions">
        <x-button :href="route('admin.invoices.index', ['status' => 'overdue'])" variant="secondary" icon="banknote">Catat tunai</x-button>
    </x-slot>

    <div x-data="{
            p: null,
            open(data) { this.p = data; $dispatch('open-modal', 'bukti') },
        }">
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <x-tabs :tabs="$tabs" :active="$status->value" class="lg:flex-1" />
            <form method="GET" data-no-lock class="lg:w-64">
                <input type="hidden" name="status" value="{{ $status->value }}">
                <label for="property" class="sr-only">Gedung</label>
                <select id="property" name="property" class="field" onchange="this.form.submit()">
                    <option value="">Semua gedung</option>
                    @foreach ($properties as $id => $name)
                        <option value="{{ $id }}" @selected((int) request('property') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($payments->isEmpty())
            <div class="card">
                <x-empty-state :icon="$status === PaymentStatus::Pending ? 'badge-check' : 'wallet'"
                    :title="$status === PaymentStatus::Pending ? 'Semua bukti sudah diperiksa' : 'Belum ada data'"
                    :description="$status === PaymentStatus::Pending ? 'Bukti transfer baru dari penyewa akan muncul di sini.' : null" />
            </div>
        @else
            <x-table>
                <x-slot name="head">
                    <th>Penyewa</th><th>Tagihan</th><th>Metode</th><th>Tanggal bayar</th><th class="is-money">Nominal</th>
                    <th>{{ $status === PaymentStatus::Pending ? 'Dikirim' : 'Diproses' }}</th><th><span class="sr-only">Aksi</span></th>
                </x-slot>
                @foreach ($payments as $payment)
                    @php
                        $invoice = $payment->invoice;
                        $lease = $invoice->lease;
                        $data = [
                            'id' => $payment->id,
                            'tenant' => $lease->user->name,
                            'room' => $lease->room->property->name.', '.$lease->room->label,
                            'invoice' => $invoice->number,
                            'period' => $invoice->periodLabel(),
                            'amount' => rupiah($payment->amount),
                            'invoiceAmount' => rupiah($invoice->amount),
                            'matches' => $payment->amount === $invoice->amount,
                            'paidAt' => tanggal($payment->paid_at),
                            'method' => $payment->method->label(),
                            'proof' => $payment->hasProof() ? route('payments.proof', $payment) : null,
                            'pdf' => $payment->proofIsPdf(),
                            'pending' => $payment->status === PaymentStatus::Pending,
                            'verifyUrl' => route('admin.payments.verify', $payment),
                            'rejectUrl' => route('admin.payments.reject', $payment),
                        ];
                    @endphp
                    <tr>
                        <td data-label="Penyewa">
                            <span class="block font-semibold text-ink-900">{{ $lease->user->name }}</span>
                            <span class="block text-xs text-ink-500">{{ $lease->room->property->name }}, {{ $lease->room->code }}</span>
                        </td>
                        <td data-label="Tagihan">
                            <a href="{{ route('admin.leases.show', $lease) }}" class="num block whitespace-nowrap font-medium text-ink-900 hover:text-tegel-700">{{ $invoice->number }}</a>
                            <span class="block text-xs text-ink-500">{{ $invoice->periodLabel() }}</span>
                        </td>
                        <td data-label="Metode">
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                <x-dynamic-component :component="'lucide-'.$payment->method->icon()" class="h-4 w-4 text-ink-400" stroke-width="1.75" aria-hidden="true" />
                                {{ $payment->method->label() }}
                            </span>
                        </td>
                        <td data-label="Tanggal bayar" class="num whitespace-nowrap">{{ tanggal($payment->paid_at) }}</td>
                        <td data-label="Nominal" class="is-money font-medium text-ink-900"><x-money :amount="$payment->amount" /></td>
                        <td data-label="{{ $status === PaymentStatus::Pending ? 'Dikirim' : 'Diproses' }}" class="whitespace-nowrap">
                            @if ($status === PaymentStatus::Pending)
                                {{ $payment->created_at->locale('id')->diffForHumans() }}
                            @else
                                <span class="block">{{ $payment->verifier?->name ?? '-' }}</span>
                                <span class="num block text-xs text-ink-500">{{ $payment->verified_at ? tanggal($payment->verified_at) : '' }}</span>
                                @if ($payment->reject_reason)
                                    <span class="block max-w-xs whitespace-normal text-xs text-danger">{{ $payment->reject_reason }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="is-actions">
                            @if ($status === PaymentStatus::Pending)
                                <x-button size="sm" icon="eye" x-on:click="open(@js($data))">Periksa</x-button>
                            @else
                                @if ($payment->hasProof())
                                    <x-button size="sm" variant="ghost" icon="image" x-on:click="open(@js($data))">Bukti</x-button>
                                @endif
                                @if ($isOwner && $status === PaymentStatus::Verified)
                                    <x-button size="sm" variant="ghost" class="text-danger hover:bg-danger-soft" x-data x-on:click="$dispatch('open-modal', 'batal-verif-{{ $payment->id }}')">Batalkan</x-button>
                                    <x-confirm-modal :name="'batal-verif-'.$payment->id" title="Batalkan verifikasi?" :action="route('admin.payments.revoke', $payment)" confirm="Batalkan verifikasi" :reason="true" reason-label="Alasan koreksi">
                                        Tagihan {{ $invoice->number }} kembali belum lunas dan jumlah terbayar kontrak dikurangi {{ rupiah($payment->amount) }}. Data pembayaran tidak dihapus dan koreksi tercatat di log.
                                    </x-confirm-modal>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$payments" />
        @endif

        {{-- Pratinjau bukti besar + aksi --}}
        <x-modal name="bukti" max-width="4xl">
            <template x-if="p">
                <div class="grid md:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                    <div class="flex min-h-[20rem] items-center justify-center bg-ink-900 md:min-h-[32rem]">
                        <template x-if="p.proof && ! p.pdf">
                            <a :href="p.proof" target="_blank" rel="noopener" class="block h-full w-full">
                                <img :src="p.proof" :alt="'Bukti bayar ' + p.invoice" class="h-full max-h-[70vh] w-full object-contain">
                            </a>
                        </template>
                        <template x-if="p.proof && p.pdf">
                            <iframe :src="p.proof" class="h-[70vh] w-full bg-white" :title="'Bukti bayar ' + p.invoice"></iframe>
                        </template>
                        <template x-if="! p.proof">
                            <p class="text-sm text-kapur-200">Tidak ada berkas bukti (pembayaran tunai).</p>
                        </template>
                    </div>

                    <div class="flex flex-col gap-5 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold text-ink-500">Pembayaran</p>
                                <h2 class="font-display text-lg font-semibold" x-text="p.tenant"></h2>
                                <p class="text-sm text-ink-500" x-text="p.room"></p>
                            </div>
                            <button type="button" x-on:click="$dispatch('close')" class="rounded-lg p-1.5 text-ink-500 hover:bg-kapur-100">
                                <span class="sr-only">Tutup</span>
                                <x-lucide-x class="h-5 w-5" stroke-width="1.75" aria-hidden="true" />
                            </button>
                        </div>

                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-ink-500">Tagihan</dt><dd class="num font-medium" x-text="p.invoice"></dd></div>
                            <div><dt class="text-ink-500">Periode</dt><dd class="font-medium" x-text="p.period"></dd></div>
                            <div><dt class="text-ink-500">Tanggal bayar</dt><dd class="num font-medium" x-text="p.paidAt"></dd></div>
                            <div><dt class="text-ink-500">Metode</dt><dd class="font-medium" x-text="p.method"></dd></div>
                            <div class="col-span-2 rounded-lg p-3" :class="p.matches ? 'bg-success-soft' : 'bg-danger-soft'">
                                <dt class="text-ink-500">Nominal dibayar</dt>
                                <dd class="num font-display text-xl font-semibold" x-text="p.amount"></dd>
                                <dd class="mt-0.5 text-xs" :class="p.matches ? 'text-success' : 'text-danger'"
                                    x-text="p.matches ? 'Sama dengan nominal tagihan' : 'Berbeda dari tagihan ' + p.invoiceAmount"></dd>
                            </div>
                        </dl>

                        <template x-if="p.pending">
                            <div class="mt-auto space-y-3">
                                <form method="POST" :action="p.verifyUrl">
                                    @csrf @method('PATCH')
                                    <x-button type="submit" icon="badge-check" class="w-full">Verifikasi, tandai lunas</x-button>
                                </form>
                                <form method="POST" :action="p.rejectUrl" class="rounded-xl border border-kapur-200 p-3">
                                    @csrf @method('PATCH')
                                    <x-textarea name="reason" label="Alasan penolakan" rows="2" maxlength="500" required minlength="10" id="reject-payment-reason"
                                        hint="Contoh: nominal pada bukti tidak terbaca." />
                                    <x-button type="submit" variant="danger" size="sm" class="mt-2 w-full">Tolak bukti</x-button>
                                </form>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </x-modal>
    </div>
</x-layouts.admin>
