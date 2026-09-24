@php
    $labels = ['pending' => 'Menunggu verifikasi', 'tenants' => 'Penyewa', 'managers' => 'Pengelola'];
    $tabItems = collect($tabs)->mapWithKeys(fn ($t) => [$t => [
        'label' => $labels[$t],
        'url' => route('admin.users.index', ['tab' => $t]),
        'count' => $counts[$t],
    ]])->all();
    $isOwner = auth()->user()->isOwner();
    $reopenManager = $errors->any() && old('_form') === 'manager';
@endphp

<x-layouts.admin heading="Pengguna" :subheading="$isOwner ? 'Verifikasi penyewa baru dan kelola akun pengelola.' : 'Penyewa yang pernah mengajukan atau menyewa di gedungmu.'"
    :breadcrumb="['Pengguna' => null]">
    @if ($isOwner)
        <x-slot name="actions">
            <x-button icon="user-plus" x-data x-on:click="$dispatch('open-modal', 'manager-form')">Tambah pengelola</x-button>
        </x-slot>
    @endif

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <x-tabs :tabs="$tabItems" :active="$tab" class="lg:flex-1" />
        <form method="GET" data-no-lock class="flex gap-2 lg:w-96">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative flex-1">
                <label for="q" class="sr-only">Cari pengguna</label>
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" stroke-width="2" aria-hidden="true" />
                <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Nama, email, atau HP" class="field pl-9">
            </div>
            @if ($tab === 'tenants')
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="field w-36" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    @foreach (\App\Enums\UserStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            @endif
        </form>
    </div>

    @if ($users->isEmpty())
        <div class="card">
            <x-empty-state :icon="$tab === 'pending' ? 'badge-check' : 'users'"
                :title="$tab === 'pending' ? 'Semua pendaftar sudah diverifikasi' : 'Belum ada pengguna'"
                :description="$tab === 'pending' ? 'Pendaftar baru akan muncul di sini.' : 'Coba ubah kata kunci pencarian.'" />
        </div>
    @else
        <x-table>
            <x-slot name="head">
                <th>Nama</th><th>Kontak</th>
                @if ($tab === 'managers') <th>Gedung</th> @else <th>Instansi</th><th>Kontrak</th> @endif
                <th>Status</th><th><span class="sr-only">Aksi</span></th>
            </x-slot>
            @foreach ($users as $u)
                <tr>
                    <td data-label="Nama">
                        <span class="flex items-center gap-3">
                            <x-avatar :user="$u" size="sm" />
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-900">{{ $u->name }}</span>
                                <span class="block text-xs text-ink-500">{{ $u->gender->label() }} · daftar {{ tanggal($u->created_at) }}</span>
                            </span>
                        </span>
                    </td>
                    <td data-label="Kontak">
                        <span class="block">{{ $u->email }}</span>
                        <span class="num block text-xs text-ink-500">{{ $u->phone }}</span>
                    </td>
                    @if ($tab === 'managers')
                        <td data-label="Gedung">{{ $u->managedProperties->pluck('name')->join(', ') ?: '-' }}</td>
                    @else
                        <td data-label="Instansi">{{ $u->instance?->name ?? '-' }}</td>
                        <td data-label="Kontrak">
                            @if ($u->activeLease)
                                {{ $u->activeLease->room->property->name }}, {{ $u->activeLease->room->code }}
                            @else
                                <span class="text-ink-400">-</span>
                            @endif
                        </td>
                    @endif
                    <td data-label="Status"><x-status-badge :status="$u->status" size="sm" /></td>
                    <td class="is-actions">
                        @if ($isOwner && $u->isPending())
                            <form method="POST" action="{{ route('admin.users.approve', $u) }}" class="inline">
                                @csrf @method('PATCH')
                                <x-button type="submit" size="sm" icon="check">Setujui</x-button>
                            </form>
                            <x-button size="sm" variant="ghost" class="text-danger hover:bg-danger-soft" x-data x-on:click="$dispatch('open-modal', 'tolak-user-{{ $u->id }}')">Tolak</x-button>
                            <x-confirm-modal :name="'tolak-user-'.$u->id" :title="'Tolak pendaftaran '.$u->name.'?'" :action="route('admin.users.reject', $u)" confirm="Tolak pendaftaran" :reason="true" reason-label="Alasan penolakan">
                                Penyewa tidak akan bisa masuk dan akan melihat alasan ini saat mencoba login.
                            </x-confirm-modal>
                        @elseif ($tab !== 'managers' || $isOwner)
                            <x-button :href="route('admin.users.show', $u)" size="sm" variant="secondary">Detail</x-button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$users" />
    @endif

    {{-- Tambah pengelola --}}
    @if ($isOwner)
        <x-modal name="manager-form" title="Tambah pengelola" description="Akun pengelola langsung aktif dan bisa masuk ke panel admin." :show="$reopenManager" max-width="lg">
            <form method="POST" action="{{ route('admin.users.managers.store') }}" class="space-y-5 p-5 sm:p-6">
                @csrf
                <input type="hidden" name="_form" value="manager">
                <x-input name="name" label="Nama lengkap" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input name="email" type="email" label="Email" required />
                    <x-input name="phone" type="tel" label="Nomor HP" required />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select name="gender" label="Jenis kelamin" :options="\App\Enums\Gender::options()" placeholder="Pilih" required />
                    <x-input name="password" type="password" label="Kata sandi awal" required hint="Minimal 8 karakter, huruf dan angka." autocomplete="new-password" />
                </div>
                <fieldset>
                    <legend class="label">Tugaskan ke gedung</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($properties as $property)
                            <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-kapur-200 px-3 py-2.5 text-sm has-[:checked]:border-tegel-600 has-[:checked]:bg-tegel-50">
                                <input type="checkbox" name="properties[]" value="{{ $property->id }}" @checked(in_array($property->id, old('properties', [])))
                                    class="mt-0.5 h-4 w-4 rounded border-kapur-300 text-tegel-700 focus:ring-tegel-600/30">
                                <span>
                                    {{ $property->name }}
                                    @if ($property->manager_id)
                                        <span class="block text-xs text-warning">Menggantikan pengelola saat ini</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="flex justify-end gap-2 border-t border-kapur-100 pt-5">
                    <x-button variant="secondary" x-on:click="$dispatch('close-modal', 'manager-form')">Batal</x-button>
                    <x-button type="submit">Buat akun</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</x-layouts.admin>
