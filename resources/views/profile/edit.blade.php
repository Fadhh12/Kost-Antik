<x-dynamic-component :component="$user->isStaff() ? 'layouts.admin' : 'layouts.tenant'" title="Profil saya" heading="Profil saya" subheading="Data ini dipakai pengelola untuk menghubungimu.">
    <div class="grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        {{-- Data diri --}}
        <section class="card p-5 sm:p-6" aria-labelledby="data-diri">
            <h2 id="data-diri" class="font-display text-lg font-semibold">Data diri</h2>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5"
                x-data="{ preview: null, remove: false }">
                @csrf
                @method('PATCH')

                <div class="flex items-center gap-5">
                    <div class="relative">
                        <template x-if="preview">
                            <img :src="preview" alt="Pratinjau foto" class="h-20 w-20 rounded-full object-cover ring-2 ring-white">
                        </template>
                        <div x-show="! preview && ! remove"><x-avatar :user="$user" size="lg" class="!h-20 !w-20" /></div>
                        <div x-show="! preview && remove" x-cloak class="flex h-20 w-20 items-center justify-center rounded-full bg-kapur-100 text-ink-400">
                            <x-lucide-user-round class="h-8 w-8" stroke-width="1.5" aria-hidden="true" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="photo" class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg border border-kapur-300 bg-white px-3 text-sm font-semibold shadow-tile transition hover:bg-tegel-50">
                            <x-lucide-camera class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />
                            Ganti foto
                        </label>
                        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                            x-on:change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null; remove = false">
                        @if ($user->photo_path)
                            <label class="flex items-center gap-2 text-xs text-ink-500">
                                <input type="checkbox" name="remove_photo" value="1" x-model="remove" class="h-3.5 w-3.5 rounded border-kapur-300 text-danger focus:ring-danger/30">
                                Hapus foto
                            </label>
                        @endif
                        <p class="text-xs text-ink-500">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                        <x-input-error for="photo" />
                    </div>
                </div>

                <x-input name="name" label="Nama lengkap" :value="$user->name" required autocomplete="name" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input name="email" type="email" label="Email" :value="$user->email" required autocomplete="email" />
                    <x-input name="phone" type="tel" label="Nomor HP (WhatsApp)" :value="$user->phone" required autocomplete="tel" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    @if ($user->isPending())
                        <x-select name="gender" label="Jenis kelamin" :options="\App\Enums\Gender::options()" :value="$user->gender" required />
                    @else
                        <div>
                            <p class="label">Jenis kelamin</p>
                            <p class="flex h-10 items-center rounded-lg bg-kapur-100 px-3 text-sm text-ink-700">{{ $user->gender->label() }}</p>
                            <p class="mt-1.5 text-xs text-ink-500">Terkunci setelah akun diverifikasi. Hubungi pengelola bila keliru.</p>
                        </div>
                    @endif
                    @if ($user->isTenant())
                        <x-select name="instance_id" label="Kampus atau kantor" :options="$instances" :value="$user->instance_id" placeholder="Tidak ada" />
                    @endif
                </div>

                <div class="flex justify-end border-t border-kapur-100 pt-5">
                    <x-button type="submit">Simpan perubahan</x-button>
                </div>
            </form>
        </section>

        <div class="space-y-6">
            {{-- Status akun --}}
            <section class="card p-5" aria-labelledby="status-akun">
                <h2 id="status-akun" class="font-display text-base font-semibold">Status akun</h2>
                <div class="mt-3 flex items-center gap-2">
                    <x-status-badge :status="$user->status" />
                    <span class="text-sm text-ink-500">{{ $user->roleLabel() }}</span>
                </div>
                <p class="mt-3 text-xs text-ink-500">Terdaftar sejak {{ tanggal($user->created_at) }}</p>
            </section>

            {{-- Ganti kata sandi --}}
            <section class="card p-5" aria-labelledby="ganti-sandi">
                <h2 id="ganti-sandi" class="font-display text-base font-semibold">Ganti kata sandi</h2>
                <form method="POST" action="{{ route('password.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    @foreach ([['current_password', 'Kata sandi saat ini', 'current-password'], ['password', 'Kata sandi baru', 'new-password'], ['password_confirmation', 'Ulangi kata sandi baru', 'new-password']] as [$field, $label, $auto])
                        <div>
                            <label for="pw-{{ $field }}" class="label">{{ $label }}</label>
                            <input id="pw-{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $auto }}" required
                                @class(['field', 'field-error' => $errors->updatePassword->has($field)])>
                            <x-input-error :messages="$errors->updatePassword->get($field)" />
                        </div>
                    @endforeach

                    <x-button type="submit" variant="secondary" class="w-full">Perbarui kata sandi</x-button>
                </form>
            </section>
        </div>
    </div>
</x-dynamic-component>
