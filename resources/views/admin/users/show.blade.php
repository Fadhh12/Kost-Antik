@php
    $isOwner = auth()->user()->isOwner();
@endphp

<x-layouts.admin :title="$user->name" :heading="$user->name" :subheading="$user->roleLabel().' · terdaftar '.tanggal($user->created_at)"
    :breadcrumb="['Pengguna' => route('admin.users.index'), $user->name => null]">
    @if ($isOwner && ! $user->isOwner())
        <x-slot name="actions">
            @if ($user->isPending())
                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                    @csrf @method('PATCH')
                    <x-button type="submit" icon="check">Setujui akun</x-button>
                </form>
                <x-button variant="secondary" x-data x-on:click="$dispatch('open-modal', 'tolak-user')">Tolak</x-button>
            @elseif ($user->status === \App\Enums\UserStatus::Inactive)
                <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                    @csrf @method('PATCH')
                    <x-button type="submit" icon="rotate-ccw">Aktifkan kembali</x-button>
                </form>
            @elseif ($user->isAccepted())
                <x-button variant="secondary" icon="ban" class="text-danger" x-data x-on:click="$dispatch('open-modal', 'nonaktif-user')">Nonaktifkan</x-button>
            @endif
        </x-slot>
    @endif

    <div class="grid gap-6 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <aside class="card h-fit p-5">
            <div class="flex items-center gap-4">
                <x-avatar :user="$user" size="lg" />
                <div>
                    <x-status-badge :status="$user->status" />
                    @if ($user->status_reason)
                        <p class="mt-2 text-xs text-ink-500">{{ $user->status_reason }}</p>
                    @endif
                </div>
            </div>
            <dl class="mt-5 space-y-3 text-sm">
                <div><dt class="text-ink-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
                <div><dt class="text-ink-500">Nomor HP</dt><dd class="num font-medium"><a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $user->phone)) }}" target="_blank" rel="noopener" class="link">{{ $user->phone }}</a></dd></div>
                <div><dt class="text-ink-500">Jenis kelamin</dt><dd class="font-medium">{{ $user->gender->label() }}</dd></div>
                @if ($user->isTenant())
                    <div><dt class="text-ink-500">Instansi</dt><dd class="font-medium">{{ $user->instance?->name ?? '-' }}</dd></div>
                @endif
                @if ($user->isManager())
                    <div><dt class="text-ink-500">Gedung yang dikelola</dt><dd class="font-medium">{{ $user->managedProperties->pluck('name')->join(', ') ?: '-' }}</dd></div>
                @endif
            </dl>
        </aside>

        <div class="space-y-6">
            <section>
                <h2 class="mb-3 font-display text-lg font-semibold">Kontrak</h2>
                @if ($leases->isEmpty())
                    <div class="card"><x-empty-state icon="file-signature" title="Belum ada kontrak" compact /></div>
                @else
                    <x-table>
                        <x-slot name="head"><th>Kode</th><th>Kamar</th><th>Periode</th><th class="is-money">Dibayar</th><th>Status</th></x-slot>
                        @foreach ($leases as $lease)
                            <tr>
                                <td data-label="Kode">
                                    @if (Route::has('admin.leases.show'))
                                        <a href="{{ route('admin.leases.show', $lease) }}" class="num font-medium text-ink-900 hover:text-tegel-700">{{ $lease->code }}</a>
                                    @else
                                        <span class="num">{{ $lease->code }}</span>
                                    @endif
                                </td>
                                <td data-label="Kamar">{{ $lease->room->property->name }}, {{ $lease->room->code }}</td>
                                <td data-label="Periode" class="num">{{ tanggal($lease->start_date) }} - {{ tanggal($lease->end_date) }}</td>
                                <td data-label="Dibayar" class="is-money"><x-money :amount="$lease->paid_amount" /> <span class="text-ink-400">/ <x-money :amount="$lease->total_amount" /></span></td>
                                <td data-label="Status"><x-status-badge :status="$lease->status" size="sm" /></td>
                            </tr>
                        @endforeach
                    </x-table>
                @endif
            </section>

            <section>
                <h2 class="mb-3 font-display text-lg font-semibold">Pengajuan sewa terakhir</h2>
                @if ($bookings->isEmpty())
                    <div class="card"><x-empty-state icon="calendar-check" title="Belum ada pengajuan" compact /></div>
                @else
                    <x-table>
                        <x-slot name="head"><th>Kamar</th><th>Mulai</th><th>Durasi</th><th>Status</th></x-slot>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td data-label="Kamar">{{ $booking->room->property->name }}, {{ $booking->room->code }}</td>
                                <td data-label="Mulai" class="num">{{ tanggal($booking->start_date) }}</td>
                                <td data-label="Durasi" class="num">{{ $booking->duration_months }} bulan</td>
                                <td data-label="Status"><x-status-badge :status="$booking->status" size="sm" /></td>
                            </tr>
                        @endforeach
                    </x-table>
                @endif
            </section>
        </div>
    </div>

    @if ($isOwner)
        <x-confirm-modal name="tolak-user" :title="'Tolak pendaftaran '.$user->name.'?'" :action="route('admin.users.reject', $user)" confirm="Tolak pendaftaran" :reason="true" reason-label="Alasan penolakan">
            Penyewa tidak akan bisa masuk dan akan melihat alasan ini saat mencoba login.
        </x-confirm-modal>
        <x-confirm-modal name="nonaktif-user" :title="'Nonaktifkan '.$user->name.'?'" :action="route('admin.users.deactivate', $user)" confirm="Nonaktifkan" :reason="true" reason-label="Alasan">
            Akun tidak bisa masuk dan pengajuan sewa yang menunggu akan dibatalkan. Data kontrak dan pembayaran tetap tersimpan.
            @if ($user->isManager()) Gedung yang dikelolanya menjadi tanpa pengelola. @endif
        </x-confirm-modal>
    @endif
</x-layouts.admin>
