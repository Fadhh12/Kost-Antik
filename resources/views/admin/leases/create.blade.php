@php
    $tenantData = $tenants->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'phone' => $t->phone, 'gender' => $t->gender->value, 'genderLabel' => $t->gender->label()])->values();
    $propertyData = $properties->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'target' => $p->gender_target->value,
        'targetLabel' => $p->gender_target->label(),
        'rooms' => $p->rooms->map(fn ($r) => ['id' => $r->id, 'code' => $r->code, 'floor' => $r->floor, 'price' => $r->monthly_price])->values(),
    ])->values();
@endphp

<x-layouts.admin heading="Kontrak baru" subheading="Untuk penyewa walk-in. Syarat sama seperti pengajuan online."
    :breadcrumb="['Kontrak' => route('admin.leases.index'), 'Kontrak baru' => null]">
    <form method="POST" action="{{ route('admin.leases.store') }}" class="grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]"
        x-data="{
            tenants: @js($tenantData),
            properties: @js($propertyData),
            tenantId: @js((int) old('user_id') ?: null),
            propertyId: null,
            roomId: @js((int) old('room_id') ?: null),
            start: @js(old('start_date', today()->toDateString())),
            duration: @js((int) old('duration_months', 6)),
            get tenant() { return this.tenants.find(t => t.id === Number(this.tenantId)) },
            get allowedProperties() {
                if (! this.tenant) return this.properties;
                return this.properties.filter(p => p.target === 'mixed' || p.target === this.tenant.gender);
            },
            get property() { return this.properties.find(p => p.id === Number(this.propertyId)) },
            get room() { return this.property?.rooms.find(r => r.id === Number(this.roomId)) },
            get schedule() {
                if (! this.room || ! this.start) return [];
                const rows = [];
                const base = new Date(this.start + 'T00:00:00');
                for (let n = 0; n < this.duration; n++) {
                    const d = new Date(base); const day = d.getDate();
                    d.setMonth(d.getMonth() + n); if (d.getDate() !== day) d.setDate(0);
                    const due = new Date(d); due.setDate(due.getDate() + {{ (int) config('kost.grace_days') }});
                    rows.push({ n: n + 1, period: d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }), due: due.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) });
                }
                return rows;
            },
            rupiah(n) { return 'Rp' + Number(n).toLocaleString('id-ID') },
            init() {
                if (this.roomId) this.propertyId = this.properties.find(p => p.rooms.some(r => r.id === this.roomId))?.id ?? null;
                this.$watch('tenantId', () => { if (this.property && ! this.allowedProperties.includes(this.property)) { this.propertyId = null; this.roomId = null } });
                this.$watch('propertyId', () => this.roomId = null);
            },
        }">
        @csrf

        <div class="space-y-6">
            <x-form-errors />

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Penyewa</h2>
                <p class="mt-1 text-sm text-ink-500">Hanya penyewa terverifikasi tanpa kontrak aktif.</p>
                <div class="mt-4">
                    <label for="user_id" class="label">Pilih penyewa <span class="text-danger" aria-hidden="true">*</span></label>
                    <select id="user_id" name="user_id" x-model.number="tenantId" required class="field">
                        <option value="">Pilih penyewa</option>
                        <template x-for="t in tenants" :key="t.id">
                            <option :value="t.id" x-text="t.name + ' (' + t.genderLabel + ', ' + t.phone + ')'" :selected="t.id === tenantId"></option>
                        </template>
                    </select>
                    <x-input-error for="user_id" />
                    @if ($tenants->isEmpty())
                        <p class="mt-2 text-sm text-warning">Belum ada penyewa yang memenuhi syarat. Minta penyewa mendaftar lalu setujui akunnya di menu Pengguna.</p>
                    @endif
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Kamar</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="property_id" class="label">Gedung</label>
                        <select id="property_id" x-model.number="propertyId" class="field">
                            <option value="">Pilih gedung</option>
                            <template x-for="p in allowedProperties" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (' + p.targetLabel + ', ' + p.rooms.length + ' kosong)'" :selected="p.id === propertyId"></option>
                            </template>
                        </select>
                        <p class="mt-1.5 text-xs text-ink-500" x-show="tenant">Disaring sesuai jenis kelamin penyewa.</p>
                    </div>
                    <div>
                        <label for="room_id" class="label">Kamar tersedia <span class="text-danger" aria-hidden="true">*</span></label>
                        <select id="room_id" name="room_id" x-model.number="roomId" required class="field" :disabled="! property">
                            <option value="">Pilih kamar</option>
                            <template x-for="r in property?.rooms ?? []" :key="r.id">
                                <option :value="r.id" x-text="'Kamar ' + r.code + ' · ' + rupiah(r.price)" :selected="r.id === roomId"></option>
                            </template>
                        </select>
                        <x-input-error for="room_id" />
                    </div>
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">Periode</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="start_date" class="label">Tanggal mulai <span class="text-danger" aria-hidden="true">*</span></label>
                        <input id="start_date" type="date" name="start_date" x-model="start" required class="field num"
                            min="{{ today()->subDays(30)->toDateString() }}" max="{{ today()->addDays(60)->toDateString() }}">
                        <p class="mt-1.5 text-xs text-ink-500">Boleh mundur sampai 30 hari untuk input data lama.</p>
                        <x-input-error for="start_date" />
                    </div>
                    <fieldset>
                        <legend class="label">Durasi <span class="text-danger" aria-hidden="true">*</span></legend>
                        <div class="grid grid-cols-4 gap-1.5">
                            @foreach (config('kost.durations') as $m)
                                <label class="cursor-pointer">
                                    <input type="radio" name="duration_months" value="{{ $m }}" x-model.number="duration" class="peer sr-only">
                                    <span class="flex h-10 items-center justify-center rounded-lg border border-kapur-300 text-sm font-semibold transition peer-checked:border-tegel-700 peer-checked:bg-tegel-700 peer-checked:text-white">{{ $m }} bln</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                </div>

                <label class="mt-5 flex items-start gap-3 rounded-lg border border-kapur-200 p-3 text-sm">
                    <input type="checkbox" name="pay_first_cash" value="1" @checked(old('pay_first_cash')) class="mt-0.5 h-4 w-4 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                    <span>
                        <span class="font-medium text-ink-900">Catat pembayaran bulan pertama secara tunai</span>
                        <span class="block text-ink-500">Tagihan pertama langsung lunas dan tercatat atas namamu.</span>
                    </span>
                </label>
            </section>
        </div>

        {{-- Pratinjau --}}
        <aside class="lg:sticky lg:top-24 lg:h-fit">
            <div class="card p-5">
                <h2 class="font-display text-base font-semibold">Pratinjau</h2>
                <template x-if="! room">
                    <p class="mt-3 text-sm text-ink-500">Pilih penyewa dan kamar untuk melihat total dan jadwal tagihan.</p>
                </template>
                <template x-if="room">
                    <div>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-ink-500">Harga/bulan</dt><dd class="num font-medium" x-text="rupiah(room.price)"></dd></div>
                            <div class="flex justify-between"><dt class="text-ink-500">Durasi</dt><dd class="num font-medium" x-text="duration + ' bulan'"></dd></div>
                            <div class="flex justify-between border-t border-kapur-100 pt-2"><dt class="font-medium">Total kontrak</dt><dd class="num font-display text-lg font-semibold text-tegel-800" x-text="rupiah(room.price * duration)"></dd></div>
                        </dl>
                        <p class="mt-4 text-xs font-semibold text-ink-500">Jadwal tagihan</p>
                        <ol class="mt-2 max-h-64 space-y-1 overflow-y-auto text-xs">
                            <template x-for="row in schedule" :key="row.n">
                                <li class="flex justify-between rounded-md bg-kapur-50 px-2.5 py-1.5">
                                    <span x-text="row.n + '. ' + row.period"></span>
                                    <span class="num text-ink-500" x-text="'jatuh tempo ' + row.due"></span>
                                </li>
                            </template>
                        </ol>
                    </div>
                </template>
                <x-button type="submit" class="mt-5 w-full" icon="file-signature">Buat kontrak</x-button>
            </div>
        </aside>
    </form>
</x-layouts.admin>
