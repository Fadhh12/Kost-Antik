@push('head')
    @vite('resources/js/map.js')
@endpush

<x-layouts.public title="Daftarkan kost kamu" description="Punya kost di sekitar Jababeka? Daftarkan ke Kost Antik supaya lebih mudah ditemukan calon penyewa.">
    <div class="mx-auto max-w-2xl px-4 pb-16 pt-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-2">
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Daftarkan kost kamu</h1>
            <p class="text-ink-500">
                Isi data kost di bawah ini. Tim kami akan meninjau sebelum kost tampil di katalog dan peta publik.
            </p>
        </div>

        <form method="POST" action="{{ route('kost.submissions.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
            @csrf

            <x-form-errors />

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Identitas kost</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-input name="name" label="Nama kost" required maxlength="150" class="sm:col-span-2" placeholder="Kost Melati Jababeka" />
                    <x-select name="gender_target" label="Peruntukan" :options="\App\Enums\GenderTarget::options()" placeholder="Pilih peruntukan" required />
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Alamat & lokasi</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-input name="address" label="Alamat" required maxlength="255" class="sm:col-span-2" placeholder="Jl. ..." />
                    <x-input name="city" label="Kota" required maxlength="100" placeholder="Cikarang" class="sm:col-span-2" />
                </div>

                <div class="mt-5">
                    <p class="label">Titik lokasi (opsional, mempermudah ditemukan di peta)</p>
                    <div class="overflow-hidden rounded-xl border border-kapur-200">
                        <div data-map data-pick data-lat="{{ old('latitude') }}" data-lng="{{ old('longitude') }}"
                            data-lat-input="#f-latitude" data-lng-input="#f-longitude" class="h-72 w-full bg-kapur-100" role="application" aria-label="Klik peta untuk memilih lokasi"></div>
                    </div>
                    <p class="mt-1.5 text-xs text-ink-500">Klik peta atau geser pin untuk menentukan lokasi. Koordinat terisi otomatis.</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <x-input name="latitude" label="Garis lintang" inputmode="decimal" />
                        <x-input name="longitude" label="Garis bujur" inputmode="decimal" />
                    </div>
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Deskripsi & foto</h2>
                <div class="mt-5 space-y-5">
                    <x-textarea name="description" label="Deskripsi" rows="4" maxlength="2000"
                        hint="Ceritakan fasilitas, akses transportasi, dan suasana sekitar." />
                    <x-file-upload name="photos[]" label="Foto kost" accept="image/jpeg,image/png,image/webp" :multiple="true"
                        hint="Sampai 5 foto, maksimal 3 MB per foto." />
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Kontak pemilik</h2>
                <p class="mt-1 text-sm text-ink-500">Dipakai tim kami untuk menghubungi kamu soal peninjauan, tidak ditampilkan publik.</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-input name="contact_name" label="Nama pemilik/pengelola" required maxlength="100" />
                    <x-input name="contact_phone" type="tel" label="Nomor WhatsApp" required maxlength="30" placeholder="081234567890" />
                </div>
            </section>

            <x-button type="submit" size="lg" class="w-full">Kirim pengajuan</x-button>
        </form>
    </div>
</x-layouts.public>
