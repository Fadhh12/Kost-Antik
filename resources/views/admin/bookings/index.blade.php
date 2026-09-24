@php
    use App\Enums\BookingStatus;

    $tabs = collect([
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kedaluwarsa',
        'all' => 'Semua',
    ])->map(fn ($label, $key) => [
        'label' => $label,
        'url' => route('admin.bookings.index', array_filter(['status' => $key, 'property' => request('property')])),
        'count' => $key === 'all' ? $counts->sum() : (int) ($counts[$key] ?? 0),
    ])->all();
    $expireDays = (int) config('kost.booking_expire_days');
@endphp

<x-layouts.admin heading="Booking" subheading="Proses pengajuan sewa dari penyewa. Pengajuan otomatis kedaluwarsa setelah {{ $expireDays }} hari."
    :breadcrumb="['Booking' => null]">

    <div x-data="{
            b: null,
            open(data) { this.b = data; $dispatch('open-slide', 'booking-detail') },
        }">
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <x-tabs :tabs="$tabs" :active="$status" class="lg:flex-1" />
            <form method="GET" data-no-lock class="lg:w-64">
                <input type="hidden" name="status" value="{{ $status }}">
                <label for="property" class="sr-only">Gedung</label>
                <select id="property" name="property" class="field" onchange="this.form.submit()">
                    <option value="">Semua gedung</option>
                    @foreach ($properties as $id => $name)
                        <option value="{{ $id }}" @selected((int) request('property') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($bookings->isEmpty())
            <div class="card">
                <x-empty-state icon="calendar-check" :title="$status === 'pending' ? 'Tidak ada pengajuan yang menunggu' : 'Belum ada data'"
                    :description="$status === 'pending' ? 'Pengajuan baru dari penyewa akan muncul di sini.' : 'Coba pilih tab atau gedung lain.'" />
            </div>
        @else
            <x-table>
                <x-slot name="head">
                    <th>Penyewa</th><th>Kamar</th><th>Mulai</th><th class="is-money">Perkiraan total</th><th>Diajukan</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
                </x-slot>
                @foreach ($bookings as $booking)
                    @php
                        $age = (int) $booking->created_at->diffInDays(now());
                        $data = [
                            'id' => $booking->id,
                            'tenant' => $booking->user->name,
                            'email' => $booking->user->email,
                            'phone' => $booking->user->phone,
                            'gender' => $booking->user->gender->label(),
                            'instance' => $booking->user->instance?->name,
                            'property' => $booking->room->property->name,
                            'room' => $booking->room->label,
                            'roomStatus' => $booking->room->status->label(),
                            'price' => rupiah($booking->room->monthly_price),
                            'start' => tanggal($booking->start_date),
                            'end' => tanggal($booking->end_date),
                            'duration' => $booking->duration_months,
                            'total' => rupiah($booking->estimated_total),
                            'note' => $booking->note,
                            'pending' => $booking->isPending(),
                            'status' => $booking->status->label(),
                            'reason' => $booking->reject_reason,
                            'approveUrl' => route('admin.bookings.approve', $booking),
                            'rejectUrl' => route('admin.bookings.reject', $booking),
                        ];
                    @endphp
                    <tr>
                        <td data-label="Penyewa">
                            <span class="block font-semibold text-ink-900">{{ $booking->user->name }}</span>
                            <span class="block text-xs text-ink-500">{{ $booking->user->gender->label() }}{{ $booking->user->instance ? ' · '.$booking->user->instance->name : '' }}</span>
                        </td>
                        <td data-label="Kamar">
                            <span class="block font-medium text-ink-900">{{ $booking->room->label }}</span>
                            <span class="block text-xs text-ink-500">{{ $booking->room->property->name }}</span>
                        </td>
                        <td data-label="Mulai" class="num whitespace-nowrap">{{ tanggal($booking->start_date) }}<span class="block text-xs text-ink-500">{{ $booking->duration_months }} bulan</span></td>
                        <td data-label="Perkiraan total" class="is-money"><x-money :amount="$booking->estimated_total" /></td>
                        <td data-label="Diajukan" class="num whitespace-nowrap">
                            {{ $booking->created_at->locale('id')->diffForHumans() }}
                            @if ($booking->isPending() && $age >= $expireDays - 2)
                                <span class="block text-xs font-semibold text-warning">Segera kedaluwarsa</span>
                            @endif
                        </td>
                        <td data-label="Status"><x-status-badge :status="$booking->status" size="sm" /></td>
                        <td class="is-actions">
                            <x-button size="sm" :variant="$booking->isPending() ? 'primary' : 'secondary'" x-on:click="open(@js($data))">
                                {{ $booking->isPending() ? 'Proses' : 'Detail' }}
                            </x-button>
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$bookings" />
        @endif

        {{-- Detail booking (A5) --}}
        <x-slide-over name="booking-detail" title="Detail pengajuan" width="lg">
            <template x-if="b">
                <div class="space-y-6">
                    <section>
                        <h3 class="text-xs font-semibold text-ink-500">Penyewa</h3>
                        <p class="mt-1 font-display text-lg font-semibold" x-text="b.tenant"></p>
                        <dl class="mt-2 space-y-1 text-sm">
                            <div class="flex gap-2"><dt class="w-24 text-ink-500">Email</dt><dd x-text="b.email"></dd></div>
                            <div class="flex gap-2"><dt class="w-24 text-ink-500">No. HP</dt><dd><a :href="'https://wa.me/' + b.phone.replace(/^0/, '62').replace(/\D/g, '')" target="_blank" rel="noopener" class="link" x-text="b.phone"></a></dd></div>
                            <div class="flex gap-2"><dt class="w-24 text-ink-500">Jenis kelamin</dt><dd x-text="b.gender"></dd></div>
                            <div class="flex gap-2"><dt class="w-24 text-ink-500">Instansi</dt><dd x-text="b.instance ?? '-'"></dd></div>
                        </dl>
                    </section>

                    <section class="rounded-xl border border-kapur-200 p-4">
                        <h3 class="text-xs font-semibold text-ink-500">Kamar yang diajukan</h3>
                        <p class="mt-1 font-semibold"><span x-text="b.room"></span>, <span x-text="b.property"></span></p>
                        <p class="text-sm text-ink-500">Status kamar saat ini: <span class="font-medium text-ink-900" x-text="b.roomStatus"></span></p>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-ink-500">Mulai</dt><dd class="num font-medium" x-text="b.start"></dd></div>
                            <div><dt class="text-ink-500">Selesai</dt><dd class="num font-medium" x-text="b.end"></dd></div>
                            <div><dt class="text-ink-500">Harga per bulan</dt><dd class="num font-medium" x-text="b.price"></dd></div>
                            <div><dt class="text-ink-500">Total (<span x-text="b.duration"></span> bln)</dt><dd class="num font-display text-lg font-semibold text-tegel-800" x-text="b.total"></dd></div>
                        </dl>
                    </section>

                    <template x-if="b.note">
                        <section>
                            <h3 class="text-xs font-semibold text-ink-500">Catatan penyewa</h3>
                            <p class="mt-1 rounded-lg bg-kapur-50 p-3 text-sm" x-text="b.note"></p>
                        </section>
                    </template>

                    <template x-if="! b.pending">
                        <section class="rounded-lg bg-kapur-50 p-3 text-sm">
                            Status: <span class="font-semibold" x-text="b.status"></span>
                            <template x-if="b.reason"><span class="block text-ink-500">Alasan: <span x-text="b.reason"></span></span></template>
                        </section>
                    </template>

                    <template x-if="b.pending">
                        <div class="space-y-4">
                            <form method="POST" :action="b.approveUrl" class="rounded-xl border border-success-line bg-success-soft p-4">
                                @csrf @method('PATCH')
                                <p class="text-sm text-ink-700">Menyetujui akan membuat kontrak, menerbitkan <span x-text="b.duration"></span> tagihan bulanan, dan mengubah kamar menjadi terisi. Pengajuan lain untuk kamar ini otomatis ditolak.</p>
                                <x-button type="submit" icon="circle-check" class="mt-3 w-full">Setujui & buat kontrak</x-button>
                            </form>

                            <form method="POST" :action="b.rejectUrl" class="rounded-xl border border-kapur-200 p-4">
                                @csrf @method('PATCH')
                                <x-textarea name="reason" label="Alasan penolakan" rows="3" maxlength="500" required minlength="10" id="reject-booking-reason"
                                    hint="Minimal 10 karakter. Akan dibaca penyewa." />
                                <x-button type="submit" variant="danger" icon="circle-x" class="mt-3 w-full">Tolak pengajuan</x-button>
                            </form>
                        </div>
                    </template>
                </div>
            </template>
        </x-slide-over>
    </div>
</x-layouts.admin>
