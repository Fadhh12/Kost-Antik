<div class="space-y-6">
    @if ($property->images->isEmpty())
        <div class="card"><x-empty-state icon="image" title="Belum ada foto" description="Gedung tanpa foto memakai ilustrasi bawaan di katalog." /></div>
    @else
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($property->images as $image)
                <li class="card overflow-hidden">
                    <div class="relative aspect-[4/3] bg-kapur-100">
                        <img src="{{ $image->url }}" alt="Foto {{ $loop->iteration }} {{ $property->name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    </div>
                    <div class="flex items-center justify-between gap-2 px-3 py-2.5">
                        @if ($image->is_cover)
                            <x-status-badge tone="success" icon="star" label="Cover" size="sm" />
                        @elseif ($manage)
                            <form method="POST" action="{{ route('admin.property-images.cover', $image) }}">
                                @csrf @method('PATCH')
                                <x-button type="submit" size="sm" variant="ghost" icon="star">Jadikan cover</x-button>
                            </form>
                        @else
                            <span></span>
                        @endif
                        @if ($manage)
                            <x-button size="sm" variant="ghost" icon="trash-2" class="text-danger hover:bg-danger-soft" x-data x-on:click="$dispatch('open-modal', 'hapus-foto-{{ $image->id }}')">
                                <span class="sr-only">Hapus foto</span>
                            </x-button>
                            <x-confirm-modal :name="'hapus-foto-'.$image->id" title="Hapus foto ini?" :action="route('admin.property-images.destroy', $image)" method="DELETE" confirm="Hapus foto">
                                @if ($image->is_cover) Foto berikutnya akan otomatis menjadi cover. @else Foto tidak akan tampil lagi di halaman kost. @endif
                            </x-confirm-modal>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($manage && $property->images->count() < 10)
        <form method="POST" action="{{ route('admin.properties.images.store', $property) }}" enctype="multipart/form-data" class="card space-y-4 p-5">
            @csrf
            <h2 class="font-display text-base font-semibold">Tambah foto</h2>
            <x-file-upload name="images[]" accept="image/jpeg,image/png,image/webp" :multiple="true"
                :hint="'Sisa slot '.(10 - $property->images->count()).' foto. JPG, PNG, atau WebP, maksimal 3 MB.'" />
            <div class="flex justify-end"><x-button type="submit" icon="upload">Unggah</x-button></div>
        </form>
    @endif
</div>
