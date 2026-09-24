@php
    $palette = [
        'Tegel' => ['bg-tegel-50', 'bg-tegel-100', 'bg-tegel-200', 'bg-tegel-400', 'bg-tegel-600', 'bg-tegel-700', 'bg-tegel-800', 'bg-tegel-900'],
        'Kapur & Ink' => ['bg-kapur-50', 'bg-kapur-100', 'bg-kapur-200', 'bg-kapur-300', 'bg-ink-300', 'bg-ink-500', 'bg-ink-700', 'bg-ink-900'],
        'Kuningan & Jati' => ['bg-kuningan-100', 'bg-kuningan-300', 'bg-kuningan-500', 'bg-kuningan-700', 'bg-jati-300', 'bg-jati-600', 'bg-jati-800'],
        'Status' => ['bg-success', 'bg-success-soft', 'bg-warning', 'bg-warning-soft', 'bg-danger', 'bg-danger-soft', 'bg-neutral', 'bg-neutral-soft'],
    ];

    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 10), 87, 10, 3, ['path' => url('/_styleguide')]);
@endphp

<x-layouts.public title="Styleguide">
    <div class="mx-auto max-w-page space-y-16 px-4 py-12 sm:px-6 lg:px-8">
        <header>
            <h1 class="font-display text-4xl font-semibold tracking-tight">Styleguide</h1>
            <p class="mt-2 text-ink-500">Seluruh komponen Kost Antik. Hanya tersedia di environment <code class="rounded bg-kapur-100 px-1.5 py-0.5 text-sm">local</code>.</p>
        </header>

        {{-- Warna --}}
        <section class="space-y-5">
            <h2 class="text-2xl font-semibold">Warna</h2>
            @foreach ($palette as $group => $swatches)
                <div>
                    <p class="mb-2 text-sm font-semibold text-ink-500">{{ $group }}</p>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                        @foreach ($swatches as $swatch)
                            <div>
                                <div class="{{ $swatch }} h-14 rounded-lg ring-1 ring-inset ring-ink-900/5"></div>
                                <p class="mt-1 truncate text-[11px] text-ink-500">{{ str_replace('bg-', '', $swatch) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Tipografi --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Tipografi</h2>
            <div class="card space-y-4 p-6">
                <p class="font-display text-5xl font-semibold tracking-tight sm:text-6xl">Kamar yang jelas</p>
                <p class="font-display text-3xl font-semibold tracking-tight">Tagihan bulan ini sudah terbit</p>
                <p class="font-display text-xl font-semibold">Fasilitas bersama</p>
                <p class="max-w-prose leading-relaxed text-ink-700">Plus Jakarta Sans untuk teks. Kamar 2A di lantai dua menghadap taman, sudah termasuk kasur, lemari, dan meja belajar. Listrik token terpisah.</p>
                <p class="text-sm text-ink-500">Teks sekunder 14px untuk keterangan dan metadata.</p>
                <p class="num font-display text-2xl font-semibold"><x-money :amount="1250000" /> · 10 Jan 2026</p>
            </div>
        </section>

        {{-- Motif --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Motif tegel kunci</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="relative h-40 overflow-hidden rounded-xl bg-tegel-900"><div class="bg-tegel absolute inset-0 opacity-20"></div></div>
                <div class="relative h-40 overflow-hidden rounded-xl bg-kapur-100"><div class="bg-tegel-light absolute inset-0 opacity-20"></div></div>
                <div class="flex h-40 flex-col justify-center gap-4 rounded-xl bg-white p-6 ring-1 ring-kapur-200">
                    <p class="text-sm text-ink-500">Pemisah section</p>
                    <x-tegel-divider />
                </div>
            </div>
        </section>

        {{-- Tombol --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Tombol</h2>
            <div class="card flex flex-wrap items-center gap-3 p-6">
                <x-button icon="send">Ajukan Sewa</x-button>
                <x-button variant="secondary" icon="sliders-horizontal">Filter</x-button>
                <x-button variant="ghost">Batal</x-button>
                <x-button variant="danger" icon="trash-2">Hapus</x-button>
                <x-button variant="brass" icon-right="arrow-right">Lihat kamar</x-button>
                <x-button size="sm">Kecil</x-button>
                <x-button size="lg" icon-right="arrow-right">Cari kost</x-button>
                <x-button class="is-loading" disabled>Memproses</x-button>
                <x-button disabled>Nonaktif</x-button>
            </div>
        </section>

        {{-- Form --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Formulir</h2>
            <form class="card grid gap-5 p-6 md:grid-cols-2" onsubmit="event.preventDefault()">
                <x-input name="sg_name" label="Nama lengkap" placeholder="Nama sesuai KTP" required />
                <x-input name="sg_phone" label="Nomor HP" type="tel" placeholder="08xxxxxxxxxx" hint="Dipakai pengelola untuk menghubungimu." />
                <x-input name="sg_price" label="Harga per bulan" prefix="Rp" type="number" value="1250000" />
                <x-select name="sg_gender" label="Peruntukan" :options="['male' => 'Putra', 'female' => 'Putri', 'mixed' => 'Campur']" placeholder="Pilih peruntukan" />
                <x-textarea name="sg_note" label="Catatan untuk pengelola" maxlength="500" class="md:col-span-2" hint="Opsional. Misal: jam kedatangan." />
                <x-file-upload name="sg_proof" label="Bukti transfer" accept="image/*,application/pdf" hint="JPG, PNG, atau PDF. Maksimal 3 MB." class="md:col-span-2" />
                <div class="flex gap-3 md:col-span-2">
                    <x-button type="submit">Simpan</x-button>
                    <x-button variant="secondary">Batal</x-button>
                </div>
            </form>
        </section>

        {{-- Badge --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Badge status</h2>
            <div class="card flex flex-wrap gap-2 p-6">
                <x-status-badge tone="success" icon="circle-check" label="Tersedia" />
                <x-status-badge tone="success" icon="badge-check" label="Lunas" />
                <x-status-badge tone="warning" icon="hourglass" label="Menunggu verifikasi" />
                <x-status-badge tone="danger" icon="clock-alert" label="Terlambat" />
                <x-status-badge tone="danger" icon="circle-x" label="Ditolak" />
                <x-status-badge tone="neutral" icon="wrench" label="Perbaikan" />
                <x-status-badge tone="neutral" icon="ban" label="Dibatalkan" />
                <x-status-badge tone="info" icon="info" label="Info" />
                <x-status-badge tone="success" label="Kecil" size="sm" />
            </div>
        </section>

        {{-- Stat --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Stat card</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card label="Okupansi" value="83,3%" icon="bed-double" hint="25 dari 30 kamar terisi" />
                <x-stat-card label="Pendapatan bulan ini" :value="rupiah(31750000)" icon="wallet" hint="dari 23 pembayaran" />
                <x-stat-card label="Tagihan terlambat" value="4" icon="clock-alert" tone="danger" hint="Total Rp5.600.000" href="#" />
                <x-stat-card label="Booking menunggu" value="2" icon="calendar-check" tone="warning" />
            </div>
        </section>

        {{-- Tabel --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Tabel responsif</h2>
            <x-table>
                <x-slot name="head">
                    <th>Invoice</th><th>Penyewa</th><th>Jatuh tempo</th><th class="is-money">Nominal</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
                </x-slot>
                @foreach ([['KA-INV-202601-0001', 'Rizky Ananda', '13 Jan 2026', 1250000, 'success', 'Lunas'], ['KA-INV-202602-0002', 'Dewi Lestari', '13 Feb 2026', 1100000, 'warning', 'Menunggu verifikasi'], ['KA-INV-202602-0003', 'Bagas Pratama', '05 Feb 2026', 950000, 'danger', 'Terlambat']] as [$no, $name, $due, $amount, $tone, $label])
                    <tr>
                        <td data-label="Invoice"><span class="font-medium text-ink-900">{{ $no }}</span></td>
                        <td data-label="Penyewa">{{ $name }}</td>
                        <td data-label="Jatuh tempo">{{ $due }}</td>
                        <td data-label="Nominal" class="is-money"><x-money :amount="$amount" /></td>
                        <td data-label="Status"><x-status-badge :tone="$tone" :label="$label" size="sm" /></td>
                        <td class="is-actions"><x-button size="sm" variant="secondary">Detail</x-button></td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$paginator" />
        </section>

        {{-- Navigasi --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Navigasi</h2>
            <div class="card space-y-6 p-6">
                <x-breadcrumb :items="['Gedung' => '#', 'Kost Melati Putri' => '#', 'Kamar 2A' => null]" />
                <x-tabs active="pending" :tabs="[
                    'pending' => ['label' => 'Menunggu', 'url' => '#', 'count' => 3],
                    'verified' => ['label' => 'Terverifikasi', 'url' => '#', 'count' => 41],
                    'rejected' => ['label' => 'Ditolak', 'url' => '#'],
                ]" />
                <x-dropdown align="left">
                    <x-slot name="trigger"><x-button variant="secondary" icon-right="chevron-down">Aksi</x-button></x-slot>
                    <x-slot name="content">
                        <x-dropdown-link href="#" icon="pencil">Ubah</x-dropdown-link>
                        <x-dropdown-link href="#" icon="image">Kelola foto</x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>
        </section>

        {{-- Overlay --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Modal, slide-over, toast</h2>
            <div class="card flex flex-wrap gap-3 p-6">
                <x-button variant="secondary" x-data x-on:click="$dispatch('open-modal', 'sg-confirm')">Modal konfirmasi</x-button>
                <x-button variant="secondary" x-data x-on:click="$dispatch('open-modal', 'sg-reject')">Modal dengan alasan</x-button>
                <x-button variant="secondary" x-data x-on:click="$dispatch('open-slide', 'sg-slide')">Slide-over</x-button>
                <x-button variant="secondary" x-data x-on:click="$dispatch('toast', { type: 'success', message: 'Booking berhasil diajukan.' })">Toast sukses</x-button>
                <x-button variant="secondary" x-data x-on:click="$dispatch('toast', { type: 'error', message: 'Kamar sudah tidak tersedia.' })">Toast gagal</x-button>
            </div>

            <x-confirm-modal name="sg-confirm" title="Akhiri kontrak ini?" action="#" confirm="Akhiri kontrak">
                Kamar 2A akan kembali tersedia dan 3 tagihan setelah tanggal berakhir akan dibatalkan. Tindakan ini tidak bisa diurungkan.
            </x-confirm-modal>
            <x-confirm-modal name="sg-reject" title="Tolak pembayaran?" action="#" confirm="Tolak pembayaran" :reason="true" reason-label="Alasan penolakan">
                Tagihan akan kembali ke status belum dibayar dan penyewa diminta mengunggah ulang bukti.
            </x-confirm-modal>
            <x-slide-over name="sg-slide" title="Tambah kamar" description="Kost Melati Putri">
                <div class="space-y-4">
                    <x-input name="sg_code" label="Kode kamar" placeholder="2A" required />
                    <x-input name="sg_floor" label="Lantai" type="number" />
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-2"><x-button variant="secondary">Batal</x-button><x-button>Simpan</x-button></div>
                </x-slot>
            </x-slide-over>
        </section>

        {{-- Empty state --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Empty state</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="card">
                    <x-empty-state icon="search-x" title="Tidak ada kost yang cocok" description="Coba longgarkan rentang harga atau hapus filter gender.">
                        <x-button variant="secondary" icon="rotate-ccw">Reset filter</x-button>
                    </x-empty-state>
                </div>
                <div class="card">
                    <x-empty-state icon="receipt-text" title="Belum ada tagihan" description="Tagihan muncul otomatis setelah kontrak sewamu aktif." compact />
                </div>
            </div>
        </section>

        <x-form-errors />
    </div>
</x-layouts.public>
