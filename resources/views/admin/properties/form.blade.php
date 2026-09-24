@php
    $editing = $property->exists;
    $manage = $editing ? auth()->user()->can('manage', $property) : true;
    $selectedFacilities = collect(old('facilities', $property->facilities?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
@endphp

<x-layouts.admin :heading="$editing ? 'Ubah '.$property->name : 'Tambah gedung'"
    :breadcrumb="$editing
        ? ['Gedung' => route('admin.properties.index'), $property->name => route('admin.properties.show', $property), 'Ubah' => null]
        : ['Gedung' => route('admin.properties.index'), 'Tambah' => null]">
    @push('head')
        @vite('resources/js/map.js')
    @endpush

    <form method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route('admin.properties.update', $property) : route('admin.properties.store') }}"
        class="max-w-4xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-form-errors />

        @unless ($manage)
            <div class="flex gap-3 rounded-xl border border-tegel-100 bg-tegel-50 p-4 text-sm text-tegel-800">
                <x-lucide-info class="h-5 w-5 shrink-0" stroke-width="1.75" aria-hidden="true" />
                Sebagai pengelola, kamu bisa mengubah alamat, lokasi, deskripsi, dan aturan. Nama, peruntukan, fasilitas, dan status diatur pemilik.
            </div>
        @endunless

        {{-- Identitas --}}
        <section class="card p-5 sm:p-6">
            <h2 class="font-display text-lg font-semibold">Identitas gedung</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-input name="name" label="Nama gedung" :value="$property->name" required maxlength="100" class="sm:col-span-2" :disabled="! $manage" />
                <x-select name="gender_target" label="Peruntukan" :options="\App\Enums\GenderTarget::options()" :value="$property->gender_target" placeholder="Pilih peruntukan" required :disabled="! $manage" />
                <x-select name="status" label="Status" :options="\App\Enums\PropertyStatus::options()" :value="$property->status" required :disabled="! $manage"
                    hint="Gedung nonaktif tidak tampil di katalog." />
                @if ($manage)
                    <x-select name="manager_id" label="Pengelola" :options="$managers" :value="$property->manager_id" placeholder="Belum ditugaskan" class="sm:col-span-2" />
                @endif
            </div>
        </section>

        {{-- Alamat & lokasi --}}
        <section class="card p-5 sm:p-6">
            <h2 class="font-display text-lg font-semibold">Alamat & lokasi</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <x-input name="address" label="Alamat" :value="$property->address" required maxlength="255" class="sm:col-span-2" />
                <div>
                    <x-input name="city" label="Kota" :value="$property->city" required maxlength="100" list="city-list" />
                    <datalist id="city-list">
                        @foreach ($cities as $city)
                            <option value="{{ $city }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="mt-5">
                <p class="label">Titik lokasi</p>
                <div class="overflow-hidden rounded-xl border border-kapur-200">
                    <div data-map data-pick data-lat="{{ old('latitude', $property->latitude) }}" data-lng="{{ old('longitude', $property->longitude) }}"
                        data-lat-input="#f-latitude" data-lng-input="#f-longitude" class="h-72 w-full bg-kapur-100" role="application" aria-label="Klik peta untuk memilih lokasi"></div>
                </div>
                <p class="mt-1.5 text-xs text-ink-500">Klik peta atau geser pin untuk menentukan lokasi. Koordinat terisi otomatis.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <x-input name="latitude" label="Garis lintang" :value="$property->latitude" inputmode="decimal" />
                    <x-input name="longitude" label="Garis bujur" :value="$property->longitude" inputmode="decimal" />
                </div>
            </div>
        </section>

        {{-- Deskripsi --}}
        <section class="card p-5 sm:p-6">
            <h2 class="font-display text-lg font-semibold">Deskripsi & aturan</h2>
            <div class="mt-5 space-y-5">
                <x-textarea name="description" label="Deskripsi" :value="$property->description" rows="5" maxlength="2000"
                    hint="Pisahkan paragraf dengan baris kosong. Ceritakan akses transportasi dan suasana." />
                <x-textarea name="rules" label="Aturan kost" :value="$property->rules" rows="5" maxlength="2000"
                    hint="Satu aturan per baris." />
            </div>
        </section>

        @if ($manage)
            {{-- Fasilitas bersama --}}
            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Fasilitas bersama</h2>
                <div class="mt-5 grid gap-2 sm:grid-cols-3">
                    @forelse ($sharedFacilities as $facility)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-kapur-200 px-3 py-2.5 text-sm transition has-[:checked]:border-tegel-600 has-[:checked]:bg-tegel-50">
                            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" @checked($selectedFacilities->contains($facility->id))
                                class="h-4 w-4 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                            <x-dynamic-component :component="$facility->iconComponent()" class="h-4 w-4 text-tegel-600" stroke-width="1.75" aria-hidden="true" />
                            {{ $facility->name }}
                        </label>
                    @empty
                        <p class="text-sm text-ink-500 sm:col-span-3">Belum ada fasilitas bersama. <a href="{{ route('admin.facilities.index') }}" class="link">Tambah di master fasilitas</a>.</p>
                    @endforelse
                </div>
            </section>

            {{-- Foto --}}
            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Foto {{ $editing ? 'tambahan' : '' }}</h2>
                <x-file-upload name="images[]" accept="image/jpeg,image/png,image/webp" :multiple="true" class="mt-5"
                    hint="JPG, PNG, atau WebP. Maksimal 10 foto, masing-masing 3 MB. Foto pertama jadi cover." />
                @if ($editing)
                    <p class="mt-3 text-sm text-ink-500">Atur cover dan hapus foto di <a href="{{ route('admin.properties.show', [$property, 'tab' => 'foto']) }}" class="link">tab Foto</a>.</p>
                @endif
            </section>
        @endif

        <div class="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-kapur-200 bg-kapur-50/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <x-button :href="$editing ? route('admin.properties.show', $property) : route('admin.properties.index')" variant="secondary">Batal</x-button>
            <x-button type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan gedung' }}</x-button>
        </div>
    </form>
</x-layouts.admin>
