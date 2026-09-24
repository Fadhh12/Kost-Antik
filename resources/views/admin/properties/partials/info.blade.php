@push('head')
    @vite('resources/js/map.js')
@endpush

<div class="grid gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
    <div class="space-y-6">
        <section class="card p-5">
            <h2 class="font-display text-base font-semibold">Deskripsi</h2>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-ink-700">
                @forelse (preg_split('/\n\s*\n/', (string) $property->description, -1, PREG_SPLIT_NO_EMPTY) as $para)
                    <p>{{ $para }}</p>
                @empty
                    <p class="text-ink-500">Belum ada deskripsi.</p>
                @endforelse
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-display text-base font-semibold">Aturan kost</h2>
            <ul class="mt-3 space-y-2 text-sm text-ink-700">
                @forelse (collect(preg_split('/\r?\n/', (string) $property->rules))->map('trim')->filter() as $line)
                    <li class="flex gap-2"><x-lucide-dot class="h-5 w-5 shrink-0 text-kuningan-700" aria-hidden="true" />{{ $line }}</li>
                @empty
                    <li class="text-ink-500">Belum ada aturan.</li>
                @endforelse
            </ul>
        </section>

        <section class="card p-5">
            <h2 class="font-display text-base font-semibold">Fasilitas bersama</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse ($property->facilities as $facility)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-kapur-200 px-3 py-1 text-sm">
                        <x-dynamic-component :component="$facility->iconComponent()" class="h-4 w-4 text-tegel-600" stroke-width="1.75" aria-hidden="true" />
                        {{ $facility->name }}
                    </span>
                @empty
                    <p class="text-sm text-ink-500">Belum ada fasilitas bersama.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="card overflow-hidden">
            @if ($property->has_location)
                <div data-map data-lat="{{ $property->latitude }}" data-lng="{{ $property->longitude }}" data-label="{{ $property->name }}" class="h-64 w-full bg-kapur-100"></div>
            @else
                <x-empty-state icon="map-pin-off" title="Lokasi belum diatur" description="Tentukan titik lokasi di form ubah gedung." compact />
            @endif
            <dl class="divide-y divide-kapur-100 text-sm">
                <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-ink-500">Alamat</dt><dd class="text-right">{{ $property->address }}</dd></div>
                <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-ink-500">Kota</dt><dd>{{ $property->city }}</dd></div>
                <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-ink-500">Slug publik</dt><dd class="font-mono text-xs">{{ $property->slug }}</dd></div>
                <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-ink-500">Dibuat</dt><dd>{{ tanggal($property->created_at) }}</dd></div>
            </dl>
        </section>

        @can('delete', $property)
            <section class="rounded-xl border border-danger-line bg-white p-5">
                <h2 class="font-display text-base font-semibold text-danger">Hapus gedung</h2>
                <p class="mt-1.5 text-sm text-ink-500">Hanya bisa dilakukan jika gedung belum pernah punya kontrak. Jika sudah, ubah statusnya menjadi nonaktif.</p>
                <x-button variant="danger" size="sm" icon="trash-2" class="mt-4" x-data x-on:click="$dispatch('open-modal', 'hapus-gedung')">Hapus gedung</x-button>
                <x-confirm-modal name="hapus-gedung" :title="'Hapus '.$property->name.'?'" :action="route('admin.properties.destroy', $property)" method="DELETE" confirm="Hapus gedung">
                    Gedung, kamar, dan fotonya tidak akan tampil lagi di mana pun. Tindakan ini tidak bisa diurungkan dari aplikasi.
                </x-confirm-modal>
            </section>
        @endcan
    </div>
</div>
