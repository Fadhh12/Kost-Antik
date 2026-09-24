<x-layouts.tenant title="Booking saya" heading="Pengajuan sewa" subheading="Status pengajuan kamar yang pernah kamu kirim.">
    <x-slot name="banner"><x-account-banner :user="auth()->user()" /></x-slot>
    <x-slot name="actions">
        <x-button :href="route('kost.index')" variant="secondary" icon="search">Cari kamar</x-button>
    </x-slot>

    @if ($bookings->isEmpty())
        <div class="card">
            <x-empty-state icon="calendar-check" title="Belum ada pengajuan" description="Pilih kamar yang tersedia di katalog, lalu tekan Ajukan sewa.">
                <x-button :href="route('kost.index')" icon="search">Cari kost</x-button>
            </x-empty-state>
        </div>
    @else
        <ul class="space-y-4">
            @foreach ($bookings as $booking)
                @php $property = $booking->room->property; @endphp
                <li class="card overflow-hidden">
                    <div class="grid sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <div class="relative hidden bg-kapur-100 sm:block">
                            <img src="{{ $property->cover_url }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <a href="{{ route('kost.show', $property) }}" class="font-display text-lg font-semibold hover:text-tegel-700">{{ $property->name }}</a>
                                    <p class="text-sm text-ink-500">{{ $booking->room->label }} · {{ $property->city }}</p>
                                </div>
                                <x-status-badge :status="$booking->status" />
                            </div>

                            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                                <div><dt class="text-ink-500">Mulai</dt><dd class="num font-medium">{{ tanggal($booking->start_date) }}</dd></div>
                                <div><dt class="text-ink-500">Durasi</dt><dd class="num font-medium">{{ $booking->duration_months }} bulan</dd></div>
                                <div><dt class="text-ink-500">Perkiraan total</dt><dd class="num font-medium"><x-money :amount="$booking->estimated_total" /></dd></div>
                                <div><dt class="text-ink-500">Diajukan</dt><dd class="num font-medium">{{ tanggal($booking->created_at) }}</dd></div>
                            </dl>

                            @if ($booking->note)
                                <p class="mt-3 text-sm text-ink-500">Catatanmu: {{ $booking->note }}</p>
                            @endif

                            @if ($booking->reject_reason)
                                <p class="mt-4 flex gap-2 rounded-lg bg-danger-soft p-3 text-sm text-danger">
                                    <x-lucide-circle-alert class="h-4 w-4 shrink-0" stroke-width="2" aria-hidden="true" />
                                    {{ $booking->reject_reason }}
                                </p>
                            @endif

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($booking->lease && Route::has('app.leases.show'))
                                    <x-button :href="route('app.leases.show', $booking->lease)" size="sm" icon-right="arrow-right">Lihat kontrak</x-button>
                                @endif
                                @can('cancel', $booking)
                                    <x-button size="sm" variant="secondary" icon="x" x-data x-on:click="$dispatch('open-modal', 'batal-{{ $booking->id }}')">Batalkan</x-button>
                                    <x-confirm-modal :name="'batal-'.$booking->id" title="Batalkan pengajuan?" :action="route('app.bookings.cancel', $booking)" confirm="Ya, batalkan">
                                        Pengajuan untuk {{ $booking->room->label }} di {{ $property->name }} akan dibatalkan. Kamu bisa mengajukan kamar lain setelahnya.
                                    </x-confirm-modal>
                                @endcan
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        <x-pagination :paginator="$bookings" />
    @endif
</x-layouts.tenant>
