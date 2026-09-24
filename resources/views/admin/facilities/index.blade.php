@php
    $reopen = $errors->any() && old('_form') === 'facility';
@endphp

<x-layouts.admin heading="Fasilitas" subheading="Daftar fasilitas yang bisa dipasang di gedung (bersama) atau kamar."
    :breadcrumb="['Master data' => null, 'Fasilitas' => null]">
    <x-slot name="actions">
        <x-button icon="plus" x-data x-on:click="$dispatch('facility-create')">Tambah fasilitas</x-button>
    </x-slot>

    <div x-data="{
            mode: @js(old('_mode', 'create')),
            item: @js(['id' => old('_id'), 'name' => old('name', ''), 'icon' => old('icon', ''), 'type' => old('type', 'room')]),
            action: @js(old('_action', route('admin.facilities.store'))),
            create() { this.mode = 'create'; this.item = { id: null, name: '', icon: '', type: 'room' }; this.action = @js(route('admin.facilities.store')); $dispatch('open-modal', 'facility-form') },
            edit(f, url) { this.mode = 'edit'; this.item = { ...f }; this.action = url; $dispatch('open-modal', 'facility-form') },
        }"
        x-on:facility-create.window="create()">

        @if ($facilities->isEmpty())
            <div class="card"><x-empty-state icon="sofa" title="Belum ada fasilitas" description="Tambahkan fasilitas seperti AC, Wi-Fi, atau parkir motor." /></div>
        @else
            <x-table>
                <x-slot name="head">
                    <th>Nama</th><th>Jenis</th><th>Dipakai</th><th><span class="sr-only">Aksi</span></th>
                </x-slot>
                @foreach ($facilities as $facility)
                    <tr>
                        <td data-label="Nama">
                            <span class="flex items-center gap-3 font-medium text-ink-900">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-tegel-50 text-tegel-700">
                                    <x-dynamic-component :component="$facility->iconComponent()" class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />
                                </span>
                                {{ $facility->name }}
                            </span>
                        </td>
                        <td data-label="Jenis">{{ $facility->type->label() }}</td>
                        <td data-label="Dipakai" class="num">{{ $facility->properties_count }} gedung · {{ $facility->rooms_count }} kamar</td>
                        <td class="is-actions">
                            <x-button size="sm" variant="ghost" icon="pencil"
                                x-on:click="edit(@js(['id' => $facility->id, 'name' => $facility->name, 'icon' => $facility->icon, 'type' => $facility->type->value]), @js(route('admin.facilities.update', $facility)))">Ubah</x-button>
                            <x-button size="sm" variant="ghost" icon="trash-2" class="text-danger hover:bg-danger-soft"
                                x-on:click="$dispatch('open-modal', 'hapus-fasilitas-{{ $facility->id }}')">Hapus</x-button>
                            <x-confirm-modal :name="'hapus-fasilitas-'.$facility->id" title="Hapus fasilitas?" :action="route('admin.facilities.destroy', $facility)" method="DELETE" confirm="Hapus">
                                {{ $facility->name }} akan dilepas dari {{ $facility->properties_count }} gedung dan {{ $facility->rooms_count }} kamar.
                            </x-confirm-modal>
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$facilities" />
        @endif

        <x-modal name="facility-form" :show="$reopen" max-width="md">
            <form method="POST" :action="action" class="space-y-5 p-5 sm:p-6">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
                <input type="hidden" name="_form" value="facility">
                <input type="hidden" name="_mode" :value="mode">
                <input type="hidden" name="_action" :value="action">

                <h2 class="font-display text-lg font-semibold" x-text="mode === 'edit' ? 'Ubah fasilitas' : 'Tambah fasilitas'"></h2>

                <x-input name="name" label="Nama" x-model="item.name" required maxlength="50" />
                <div>
                    <x-input name="icon" label="Ikon Lucide" x-model="item.icon" placeholder="wifi" hint="Nama ikon dari lucide.dev, contoh: wifi, air-vent, bike." />
                </div>
                <x-select name="type" label="Jenis" :options="\App\Enums\FacilityType::options()" x-model="item.type" required />

                <div class="flex justify-end gap-2 border-t border-kapur-100 pt-5">
                    <x-button variant="secondary" x-on:click="$dispatch('close-modal', 'facility-form')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.admin>
