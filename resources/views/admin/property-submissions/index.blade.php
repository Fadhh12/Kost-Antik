<x-layouts.admin heading="Pengajuan kost" subheading="Kost yang didaftarkan pemilik luar lewat halaman publik, menunggu ditinjau sebelum tampil di katalog."
    :breadcrumb="['Pengajuan kost' => null]">

    <div x-data="{
            s: null,
            open(data) { this.s = data; $dispatch('open-slide', 'submission-detail') },
        }">
        @if ($submissions->isEmpty())
            <div class="card">
                <x-empty-state icon="inbox" title="Belum ada pengajuan" description="Pengajuan kost baru dari halaman /daftar-kost akan muncul di sini." />
            </div>
        @else
            <x-table>
                <x-slot name="head">
                    <th>Nama kost</th><th>Kota</th><th>Pemilik</th><th>Diajukan</th><th>Status</th><th><span class="sr-only">Aksi</span></th>
                </x-slot>
                @foreach ($submissions as $submission)
                    @php
                        $data = [
                            'id' => $submission->id,
                            'name' => $submission->name,
                            'address' => $submission->address,
                            'city' => $submission->city,
                            'gender' => $submission->gender_target->label(),
                            'description' => $submission->description,
                            'contactName' => $submission->contact_name,
                            'contactPhone' => $submission->contact_phone,
                            'photos' => collect($submission->photos ?? [])->map(fn ($p) => \Illuminate\Support\Facades\Storage::disk('public')->url($p))->all(),
                            'pending' => $submission->status === \App\Enums\SubmissionStatus::Pending,
                            'status' => $submission->status->label(),
                            'reason' => $submission->rejection_reason,
                            'approveUrl' => route('admin.property-submissions.approve', $submission),
                            'rejectUrl' => route('admin.property-submissions.reject', $submission),
                        ];
                    @endphp
                    <tr>
                        <td data-label="Nama kost">
                            <span class="block font-semibold text-ink-900">{{ $submission->name }}</span>
                            <span class="block text-xs text-ink-500">{{ $submission->gender_target->label() }}</span>
                        </td>
                        <td data-label="Kota">{{ $submission->city }}</td>
                        <td data-label="Pemilik">
                            <span class="block font-medium text-ink-900">{{ $submission->contact_name }}</span>
                            <span class="block text-xs text-ink-500">{{ $submission->contact_phone }}</span>
                        </td>
                        <td data-label="Diajukan" class="num whitespace-nowrap">{{ $submission->created_at->locale('id')->diffForHumans() }}</td>
                        <td data-label="Status"><x-status-badge :status="$submission->status" size="sm" /></td>
                        <td class="is-actions">
                            <x-button size="sm" :variant="$submission->status === \App\Enums\SubmissionStatus::Pending ? 'primary' : 'secondary'" x-on:click="open(@js($data))">
                                {{ $submission->status === \App\Enums\SubmissionStatus::Pending ? 'Tinjau' : 'Detail' }}
                            </x-button>
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$submissions" />
        @endif

        <x-slide-over name="submission-detail" title="Detail pengajuan kost" width="lg">
            <template x-if="s">
                <div class="space-y-6">
                    <section>
                        <h3 class="text-xs font-semibold text-ink-500">Kost</h3>
                        <p class="mt-1 font-display text-lg font-semibold" x-text="s.name"></p>
                        <p class="text-sm text-ink-500" x-text="s.address + ', ' + s.city"></p>
                        <p class="mt-1 text-sm text-ink-700" x-text="s.gender"></p>
                    </section>

                    <template x-if="s.photos && s.photos.length">
                        <section class="grid grid-cols-3 gap-2">
                            <template x-for="url in s.photos" :key="url">
                                <img :src="url" alt="" class="aspect-square w-full rounded-lg object-cover">
                            </template>
                        </section>
                    </template>

                    <template x-if="s.description">
                        <section>
                            <h3 class="text-xs font-semibold text-ink-500">Deskripsi</h3>
                            <p class="mt-1 whitespace-pre-line text-sm text-ink-700" x-text="s.description"></p>
                        </section>
                    </template>

                    <section class="rounded-xl border border-kapur-200 p-4">
                        <h3 class="text-xs font-semibold text-ink-500">Kontak pemilik</h3>
                        <p class="mt-1 font-semibold" x-text="s.contactName"></p>
                        <a :href="'https://wa.me/' + s.contactPhone.replace(/^0/, '62').replace(/\D/g, '')" target="_blank" rel="noopener" class="link text-sm" x-text="s.contactPhone"></a>
                    </section>

                    <template x-if="! s.pending">
                        <section class="rounded-lg bg-kapur-50 p-3 text-sm">
                            Status: <span class="font-semibold" x-text="s.status"></span>
                            <template x-if="s.reason"><span class="block text-ink-500">Alasan: <span x-text="s.reason"></span></span></template>
                        </section>
                    </template>

                    <template x-if="s.pending">
                        <div class="space-y-4">
                            <form method="POST" :action="s.approveUrl" class="rounded-xl border border-success-line bg-success-soft p-4">
                                @csrf
                                <p class="text-sm text-ink-700">Menyetujui akan membuat gedung baru yang langsung tampil di katalog dan peta publik, ditugaskan ke akunmu sebagai pengelola.</p>
                                <x-button type="submit" icon="circle-check" class="mt-3 w-full">Setujui & tampilkan</x-button>
                            </form>

                            <form method="POST" :action="s.rejectUrl" class="rounded-xl border border-kapur-200 p-4">
                                @csrf
                                <x-textarea name="reason" label="Alasan penolakan" rows="3" maxlength="255" required minlength="5" id="reject-submission-reason"
                                    hint="Minimal 5 karakter." />
                                <x-button type="submit" variant="danger" icon="circle-x" class="mt-3 w-full">Tolak pengajuan</x-button>
                            </form>
                        </div>
                    </template>
                </div>
            </template>
        </x-slide-over>
    </div>
</x-layouts.admin>
