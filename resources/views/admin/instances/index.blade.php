@php
    $reopen = $errors->any() && old('_form') === 'instance';
@endphp

<x-layouts.admin heading="Instansi" subheading="Kampus dan kantor asal penyewa, dipakai saat pendaftaran."
    :breadcrumb="['Master data' => null, 'Instansi' => null]">
    <x-slot name="actions">
        <x-button icon="plus" x-data x-on:click="$dispatch('instance-create')">Tambah instansi</x-button>
    </x-slot>

    <div x-data="{
            mode: @js(old('_mode', 'create')),
            item: @js(['name' => old('name', ''), 'type' => old('type', 'campus'), 'address' => old('address', '')]),
            action: @js(old('_action', route('admin.instances.store'))),
            create() { this.mode = 'create'; this.item = { name: '', type: 'campus', address: '' }; this.action = @js(route('admin.instances.store')); $dispatch('open-modal', 'instance-form') },
            edit(i, url) { this.mode = 'edit'; this.item = { ...i }; this.action = url; $dispatch('open-modal', 'instance-form') },
        }"
        x-on:instance-create.window="create()">

        @if ($instances->isEmpty())
            <div class="card"><x-empty-state icon="graduation-cap" title="Belum ada instansi" description="Tambahkan kampus atau kantor di sekitar gedung kost." /></div>
        @else
            <x-table>
                <x-slot name="head">
                    <th>Nama</th><th>Jenis</th><th>Alamat</th><th>Penyewa</th><th><span class="sr-only">Aksi</span></th>
                </x-slot>
                @foreach ($instances as $instance)
                    <tr>
                        <td data-label="Nama"><span class="font-medium text-ink-900">{{ $instance->name }}</span></td>
                        <td data-label="Jenis">{{ $instance->type->label() }}</td>
                        <td data-label="Alamat">{{ $instance->address ?? '-' }}</td>
                        <td data-label="Penyewa" class="num">{{ $instance->users_count }}</td>
                        <td class="is-actions">
                            <x-button size="sm" variant="ghost" icon="pencil"
                                x-on:click="edit(@js(['name' => $instance->name, 'type' => $instance->type->value, 'address' => $instance->address]), @js(route('admin.instances.update', $instance)))">Ubah</x-button>
                            <x-button size="sm" variant="ghost" icon="trash-2" class="text-danger hover:bg-danger-soft"
                                x-on:click="$dispatch('open-modal', 'hapus-instansi-{{ $instance->id }}')">Hapus</x-button>
                            <x-confirm-modal :name="'hapus-instansi-'.$instance->id" title="Hapus instansi?" :action="route('admin.instances.destroy', $instance)" method="DELETE" confirm="Hapus">
                                {{ $instance->users_count }} penyewa dari {{ $instance->name }} akan tercatat tanpa instansi.
                            </x-confirm-modal>
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$instances" />
        @endif

        <x-modal name="instance-form" :show="$reopen" max-width="md">
            <form method="POST" :action="action" class="space-y-5 p-5 sm:p-6">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
                <input type="hidden" name="_form" value="instance">
                <input type="hidden" name="_mode" :value="mode">
                <input type="hidden" name="_action" :value="action">

                <h2 class="font-display text-lg font-semibold" x-text="mode === 'edit' ? 'Ubah instansi' : 'Tambah instansi'"></h2>

                <x-input name="name" label="Nama" x-model="item.name" required maxlength="150" />
                <x-select name="type" label="Jenis" :options="\App\Enums\InstanceType::options()" x-model="item.type" required />
                <x-input name="address" label="Alamat" x-model="item.address" maxlength="255" />

                <div class="flex justify-end gap-2 border-t border-kapur-100 pt-5">
                    <x-button variant="secondary" x-on:click="$dispatch('close-modal', 'instance-form')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.admin>
