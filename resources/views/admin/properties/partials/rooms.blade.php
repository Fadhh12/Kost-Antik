@php
    $reopen = $errors->any() && old('_form') === 'room';
    $canCreate = $user->can('create', [\App\Models\Room::class, $property]);
    $tileTone = [
        'available' => 'border-success-line bg-success-soft text-success hover:border-success',
        'occupied' => 'border-tegel-700 bg-tegel-700 text-white hover:bg-tegel-800',
        'maintenance' => 'border-neutral-line bg-neutral-soft text-neutral hover:border-neutral stripe-maintenance',
    ];
    $blank = ['code' => '', 'floor' => '', 'size_m2' => '', 'monthly_price' => '', 'capacity' => 1, 'status' => 'available', 'facilities' => [], 'occupied' => false];
    $oldItem = [
        'code' => old('code', ''),
        'floor' => old('floor', ''),
        'size_m2' => old('size_m2', ''),
        'monthly_price' => old('monthly_price', ''),
        'capacity' => (int) old('capacity', 1),
        'status' => old('status', 'available'),
        'facilities' => array_map('intval', old('facilities', [])),
        'occupied' => (bool) old('_occupied', false),
    ];
@endphp

<div x-data="{
        mode: @js(old('_mode', 'create')),
        action: @js(old('_action', route('admin.properties.rooms.store', $property))),
        item: @js($reopen ? $oldItem : $blank),
        create() { this.mode = 'create'; this.item = @js($blank); this.action = @js(route('admin.properties.rooms.store', $property)); $dispatch('open-slide', 'room-form') },
        edit(room, url) { this.mode = 'edit'; this.item = room; this.action = url; $dispatch('open-slide', 'room-form') },
    }">

    {{-- Ringkasan + aksi --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2 text-sm">
            <x-status-badge tone="success" icon="door-open" :label="$counts['available'].' tersedia'" />
            <x-status-badge tone="info" icon="door-closed" :label="$counts['occupied'].' terisi'" />
            <x-status-badge tone="neutral" icon="wrench" :label="$counts['maintenance'].' perbaikan'" />
        </div>
        @if ($canCreate)
            <x-button icon="plus" x-on:click="create()">Tambah kamar</x-button>
        @endif
    </div>

    @if ($rooms->isEmpty())
        <div class="card">
            <x-empty-state icon="bed-double" title="Belum ada kamar" description="Tambahkan kamar agar gedung ini bisa disewa.">
                @if ($canCreate)
                    <x-button icon="plus" x-on:click="create()">Tambah kamar</x-button>
                @endif
            </x-empty-state>
        </div>
    @else
        {{-- Denah per lantai --}}
        <section class="card mb-6 p-5" aria-labelledby="denah">
            <h2 id="denah" class="font-display text-base font-semibold">Denah kamar</h2>
            <div class="mt-4 space-y-4">
                @foreach ($rooms->groupBy(fn ($r) => $r->floor ?? 0)->sortKeysDesc() as $floor => $floorRooms)
                    <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-start gap-3">
                        <p class="pt-3 text-xs font-semibold text-ink-500">Lantai {{ $floor }}</p>
                        <div class="flex flex-wrap gap-2 rounded-xl bg-kapur-50 p-2">
                            @foreach ($floorRooms as $room)
                                @php
                                    $payload = [
                                        'code' => $room->code, 'floor' => $room->floor, 'size_m2' => $room->size_m2,
                                        'monthly_price' => $room->monthly_price, 'capacity' => $room->capacity,
                                        'status' => $room->status->value, 'facilities' => $room->facilities->pluck('id')->all(),
                                        'occupied' => $room->status === \App\Enums\RoomStatus::Occupied,
                                    ];
                                @endphp
                                <button type="button"
                                    @can('update', $room) x-on:click="edit(@js($payload), @js(route('admin.properties.rooms.update', [$property, $room])))" @else disabled @endcan
                                    class="flex h-16 w-20 flex-col items-center justify-center rounded-lg border-2 transition {{ $tileTone[$room->status->value] }}"
                                    title="Kamar {{ $room->code }}: {{ $room->status->label() }}{{ $room->activeLease ? ', '.$room->activeLease->user->name : '' }}">
                                    <span class="font-display text-base font-bold leading-none">{{ $room->code }}</span>
                                    <span class="mt-1 text-[10px] font-semibold">{{ $room->status->label() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Tabel --}}
        <x-table>
            <x-slot name="head">
                <th>Kamar</th><th>Luas</th><th>Fasilitas</th><th class="is-money">Harga/bulan</th><th>Status</th><th>Penghuni</th><th><span class="sr-only">Aksi</span></th>
            </x-slot>
            @foreach ($rooms as $room)
                @php
                    $payload = [
                        'code' => $room->code, 'floor' => $room->floor, 'size_m2' => $room->size_m2,
                        'monthly_price' => $room->monthly_price, 'capacity' => $room->capacity,
                        'status' => $room->status->value, 'facilities' => $room->facilities->pluck('id')->all(),
                        'occupied' => $room->status === \App\Enums\RoomStatus::Occupied,
                    ];
                @endphp
                <tr>
                    <td data-label="Kamar"><span class="font-semibold text-ink-900">{{ $room->code }}</span> <span class="text-ink-500">· Lt {{ $room->floor ?? '-' }}</span></td>
                    <td data-label="Luas" class="num">{{ $room->size_m2 ? rtrim(rtrim(number_format($room->size_m2, 1, ',', '.'), '0'), ',').' m²' : '-' }}</td>
                    <td data-label="Fasilitas">
                        <span class="flex flex-wrap gap-1.5">
                            @forelse ($room->facilities as $facility)
                                <span title="{{ $facility->name }}" class="flex h-7 w-7 items-center justify-center rounded-md bg-kapur-100 text-ink-700">
                                    <x-dynamic-component :component="$facility->iconComponent()" class="h-3.5 w-3.5" stroke-width="1.75" aria-hidden="true" />
                                    <span class="sr-only">{{ $facility->name }}</span>
                                </span>
                            @empty
                                <span class="text-ink-400">-</span>
                            @endforelse
                        </span>
                    </td>
                    <td data-label="Harga/bulan" class="is-money font-medium text-ink-900"><x-money :amount="$room->monthly_price" /></td>
                    <td data-label="Status"><x-status-badge :status="$room->status" size="sm" /></td>
                    <td data-label="Penghuni">
                        @if ($room->activeLease)
                            <a href="{{ Route::has('admin.leases.show') ? route('admin.leases.show', $room->activeLease) : '#' }}" class="font-medium text-ink-900 hover:text-tegel-700">{{ $room->activeLease->user->name }}</a>
                            <span class="block text-xs text-ink-500">s.d. {{ tanggal($room->activeLease->end_date) }}</span>
                        @else
                            <span class="text-ink-400">-</span>
                        @endif
                    </td>
                    <td class="is-actions">
                        @can('update', $room)
                            <x-button size="sm" variant="ghost" icon="pencil" x-on:click="edit(@js($payload), @js(route('admin.properties.rooms.update', [$property, $room])))">Ubah</x-button>
                        @endcan
                        @can('delete', $room)
                            <x-button size="sm" variant="ghost" icon="trash-2" class="text-danger hover:bg-danger-soft" x-on:click="$dispatch('open-modal', 'hapus-kamar-{{ $room->id }}')">
                                <span class="sr-only">Hapus kamar {{ $room->code }}</span>
                            </x-button>
                            <x-confirm-modal :name="'hapus-kamar-'.$room->id" title="Hapus kamar {{ $room->code }}?" :action="route('admin.properties.rooms.destroy', [$property, $room])" method="DELETE" confirm="Hapus kamar">
                                Kamar yang punya riwayat booking atau kontrak tidak bisa dihapus. Gunakan status perbaikan untuk menutupnya sementara.
                            </x-confirm-modal>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif

    {{-- Form kamar --}}
    <x-slide-over name="room-form" :title="'Kamar'" :description="$property->name" :show="$reopen">
        <form id="room-form" method="POST" :action="action" class="space-y-5">
            @csrf
            <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <input type="hidden" name="_form" value="room">
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="_action" :value="action">
            <input type="hidden" name="_occupied" :value="item.occupied ? 1 : 0">

            <p class="text-sm font-semibold text-ink-900" x-text="mode === 'edit' ? 'Ubah kamar ' + item.code : 'Kamar baru'"></p>

            <div class="grid grid-cols-2 gap-4">
                <x-input name="code" label="Kode kamar" x-model="item.code" required maxlength="20" placeholder="2A" />
                <x-input name="floor" label="Lantai" type="number" min="0" max="50" x-model="item.floor" />
                <x-input name="size_m2" label="Luas (m²)" type="number" step="0.5" min="2" max="100" x-model="item.size_m2" />
                <x-input name="capacity" label="Kapasitas" type="number" min="1" max="4" x-model="item.capacity" required />
            </div>

            <x-input name="monthly_price" label="Harga per bulan" prefix="Rp" type="number" min="100000" max="50000000" step="50000" x-model="item.monthly_price" required
                hint="Perubahan harga tidak memengaruhi kontrak yang sedang berjalan." />

            <div>
                <p class="label">Status</p>
                <template x-if="item.occupied">
                    <div>
                        <input type="hidden" name="status" value="available">
                        <p class="rounded-lg bg-tegel-50 p-3 text-sm text-tegel-800">Kamar sedang terisi. Status berubah otomatis saat kontrak berakhir.</p>
                    </div>
                </template>
                <template x-if="! item.occupied">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (\App\Enums\RoomStatus::manualOptions() as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="{{ $value }}" x-model="item.status" class="peer sr-only">
                                <span class="flex h-10 items-center justify-center rounded-lg border border-kapur-300 text-sm font-semibold transition peer-checked:border-tegel-700 peer-checked:bg-tegel-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-kuningan-500">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </template>
                <x-input-error for="status" />
            </div>

            <fieldset>
                <legend class="label">Fasilitas kamar</legend>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($roomFacilities as $facility)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-kapur-200 px-2.5 py-2 text-sm has-[:checked]:border-tegel-600 has-[:checked]:bg-tegel-50">
                            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" x-model.number="item.facilities"
                                class="h-4 w-4 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                            {{ $facility->name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </form>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-button variant="secondary" x-on:click="$dispatch('close-slide', 'room-form')">Batal</x-button>
                <x-button type="submit" form="room-form">Simpan kamar</x-button>
            </div>
        </x-slot>
    </x-slide-over>
</div>
