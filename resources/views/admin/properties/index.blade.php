<x-layouts.admin heading="Gedung & Kamar" :subheading="auth()->user()->isOwner() ? 'Semua gedung Kost Antik.' : 'Gedung yang ditugaskan kepadamu.'"
    :breadcrumb="['Gedung' => null]">
    @can('create', \App\Models\Property::class)
        <x-slot name="actions">
            <x-button :href="route('admin.properties.create')" icon="plus">Tambah gedung</x-button>
        </x-slot>
    @endcan

    <form method="GET" class="mb-4 flex flex-col gap-2 sm:flex-row" data-no-lock>
        <div class="relative flex-1 sm:max-w-xs">
            <label for="q" class="sr-only">Cari gedung</label>
            <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" stroke-width="2" aria-hidden="true" />
            <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama gedung" class="field pl-9">
        </div>
        <label for="status" class="sr-only">Status</label>
        <select id="status" name="status" class="field sm:w-44" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (\App\Enums\PropertyStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </form>

    @if ($properties->isEmpty())
        <div class="card">
            <x-empty-state icon="building-2" title="Belum ada gedung" :description="request()->hasAny(['q', 'status']) ? 'Tidak ada gedung yang cocok dengan pencarian.' : 'Tambahkan gedung pertama beserta foto dan kamarnya.'">
                @can('create', \App\Models\Property::class)
                    <x-button :href="route('admin.properties.create')" icon="plus">Tambah gedung</x-button>
                @endcan
            </x-empty-state>
        </div>
    @else
        <x-table>
            <x-slot name="head">
                <th>Gedung</th><th>Peruntukan</th><th>Kamar terisi</th><th>Pengelola</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
            </x-slot>
            @foreach ($properties as $property)
                @php
                    $bookable = $property->rooms_count - $property->maintenance_rooms_count;
                    $pct = $bookable > 0 ? round($property->occupied_rooms_count / $bookable * 100) : 0;
                @endphp
                <tr>
                    <td data-label="Gedung">
                        <a href="{{ route('admin.properties.show', $property) }}" class="flex items-center gap-3">
                            <img src="{{ $property->cover_url }}" alt="" class="h-10 w-14 shrink-0 rounded-lg object-cover">
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-900 hover:text-tegel-700">{{ $property->name }}</span>
                                <span class="block truncate text-xs text-ink-500">{{ $property->city }}</span>
                            </span>
                        </a>
                    </td>
                    <td data-label="Peruntukan">{{ $property->gender_target->label() }}</td>
                    <td data-label="Kamar terisi">
                        <div class="flex items-center gap-2.5">
                            <span class="num whitespace-nowrap font-medium text-ink-900">{{ $property->occupied_rooms_count }}/{{ $property->rooms_count }}</span>
                            <span class="hidden h-1.5 w-16 overflow-hidden rounded-full bg-kapur-100 md:block" aria-hidden="true">
                                <span class="block h-full rounded-full bg-tegel-600" style="width: {{ $pct }}%"></span>
                            </span>
                        </div>
                    </td>
                    <td data-label="Pengelola">{{ $property->manager?->name ?? '-' }}</td>
                    <td data-label="Status"><x-status-badge :status="$property->status" size="sm" /></td>
                    <td class="is-actions">
                        <x-button :href="route('admin.properties.show', $property)" size="sm" variant="secondary">Kelola</x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$properties" />
    @endif
</x-layouts.admin>
