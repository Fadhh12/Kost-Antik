<x-layouts.tenant title="Kontrak" heading="Kontrak & riwayat" subheading="Semua kontrak sewa yang pernah kamu jalani.">
    @if ($leases->isEmpty())
        <div class="card">
            <x-empty-state icon="file-signature" title="Belum ada kontrak" description="Kontrak muncul setelah pengajuan sewamu disetujui pengelola.">
                <x-button :href="route('kost.index')" icon="search">Cari kost</x-button>
            </x-empty-state>
        </div>
    @else
        <ol class="relative space-y-6 border-l-2 border-dashed border-kapur-300 pl-6 sm:pl-8">
            @foreach ($leases as $lease)
                @php $property = $lease->room->property; @endphp
                <li class="relative">
                    <span @class([
                        'absolute -left-[33px] top-6 flex h-4 w-4 items-center justify-center rounded-full ring-4 ring-kapur-50 sm:-left-[41px]',
                        'bg-tegel-700' => $lease->isActive(),
                        'bg-kapur-300' => ! $lease->isActive(),
                    ])></span>
                    <a href="{{ route('app.leases.show', $lease) }}" class="card group grid overflow-hidden transition hover:border-tegel-300 hover:shadow-lift sm:grid-cols-[9rem_minmax(0,1fr)]">
                        <div class="relative hidden bg-kapur-100 sm:block">
                            <img src="{{ $property->cover_url }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="num text-xs font-medium text-ink-500">{{ $lease->code }}</p>
                                    <h2 class="font-display text-lg font-semibold group-hover:text-tegel-700">{{ $property->name }}, {{ $lease->room->label }}</h2>
                                </div>
                                <x-status-badge :status="$lease->status" size="sm" />
                            </div>
                            <p class="num mt-2 text-sm text-ink-500">{{ tanggal($lease->start_date) }} - {{ tanggal($lease->terminated_at ?? $lease->end_date) }} · {{ $lease->duration_months }} bulan</p>
                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                                <span>Dibayar <span class="num font-semibold"><x-money :amount="$lease->paid_amount" /></span> dari <x-money :amount="$lease->total_amount" /></span>
                                @if ($lease->review)
                                    <span class="inline-flex items-center gap-1 text-ink-500"><x-lucide-star class="h-4 w-4 fill-kuningan-500 text-kuningan-500" stroke-width="1.5" aria-hidden="true" />Ulasanmu {{ $lease->review->rating }}/5</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</x-layouts.tenant>
