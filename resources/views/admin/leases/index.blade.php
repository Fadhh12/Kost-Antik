<x-layouts.admin heading="Kontrak" subheading="Kontrak sewa aktif dan riwayatnya." :breadcrumb="['Kontrak' => null]">
    @can('create', \App\Models\Lease::class)
        <x-slot name="actions">
            <x-button :href="route('admin.leases.create')" icon="plus">Kontrak baru</x-button>
        </x-slot>
    @endcan

    <form method="GET" data-no-lock class="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_12rem_14rem_auto]">
        <div class="relative">
            <label for="q" class="sr-only">Cari kontrak</label>
            <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" stroke-width="2" aria-hidden="true" />
            <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Kode kontrak atau nama penyewa" class="field pl-9">
        </div>
        <label for="status" class="sr-only">Status</label>
        <select id="status" name="status" class="field" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (\App\Enums\LeaseStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <label for="property" class="sr-only">Gedung</label>
        <select id="property" name="property" class="field" onchange="this.form.submit()">
            <option value="">Semua gedung</option>
            @foreach ($properties as $id => $name)
                <option value="{{ $id }}" @selected((int) request('property') === $id)>{{ $name }}</option>
            @endforeach
        </select>
        <label class="flex h-10 cursor-pointer items-center gap-2 rounded-lg border border-kapur-300 bg-white px-3 text-sm font-medium text-ink-700 shadow-tile has-[:checked]:border-danger has-[:checked]:bg-danger-soft has-[:checked]:text-danger">
            <input type="checkbox" name="tunggakan" value="1" @checked(request()->boolean('tunggakan')) onchange="this.form.submit()"
                class="h-4 w-4 rounded border-kapur-300 text-danger focus:ring-danger/30">
            Ada tunggakan
        </label>
    </form>

    @if ($leases->isEmpty())
        <div class="card">
            <x-empty-state icon="file-signature" title="Tidak ada kontrak" description="Kontrak dibuat otomatis saat booking disetujui, atau manual lewat tombol Kontrak baru." />
        </div>
    @else
        <x-table>
            <x-slot name="head">
                <th>Kontrak</th><th>Penyewa</th><th>Kamar</th><th>Periode</th><th class="is-money">Dibayar</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
            </x-slot>
            @foreach ($leases as $lease)
                <tr>
                    <td data-label="Kontrak"><span class="num font-medium text-ink-900">{{ $lease->code }}</span></td>
                    <td data-label="Penyewa">{{ $lease->user->name }}</td>
                    <td data-label="Kamar">
                        <span class="block">{{ $lease->room->property->name }}</span>
                        <span class="block text-xs text-ink-500">{{ $lease->room->label }}</span>
                    </td>
                    <td data-label="Periode" class="num whitespace-nowrap">
                        {{ tanggal($lease->start_date) }}
                        <span class="block text-xs text-ink-500">s.d. {{ tanggal($lease->end_date) }}</span>
                    </td>
                    <td data-label="Dibayar" class="is-money">
                        <x-money :amount="$lease->paid_amount" />
                        <span class="block text-xs text-ink-500">dari <x-money :amount="$lease->total_amount" /></span>
                    </td>
                    <td data-label="Status">
                        <span class="flex flex-wrap gap-1">
                            <x-status-badge :status="$lease->status" size="sm" />
                            @if ($lease->hasArrears())
                                <x-status-badge tone="danger" icon="clock-alert" label="Ada tunggakan" size="sm" />
                            @endif
                        </span>
                    </td>
                    <td class="is-actions"><x-button :href="route('admin.leases.show', $lease)" size="sm" variant="secondary">Detail</x-button></td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$leases" />
    @endif
</x-layouts.admin>
