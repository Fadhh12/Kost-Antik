<x-layouts.admin heading="Ulasan" subheading="Ulasan yang disembunyikan tidak dihitung dalam rating rata-rata gedung." :breadcrumb="['Ulasan' => null]">
    <form method="GET" data-no-lock class="mb-4 grid gap-2 sm:grid-cols-3 lg:w-2/3">
        <label for="property" class="sr-only">Gedung</label>
        <select id="property" name="property" class="field" onchange="this.form.submit()">
            <option value="">Semua gedung</option>
            @foreach ($properties as $id => $name)
                <option value="{{ $id }}" @selected((int) request('property') === $id)>{{ $name }}</option>
            @endforeach
        </select>
        <label for="tampil" class="sr-only">Visibilitas</label>
        <select id="tampil" name="tampil" class="field" onchange="this.form.submit()">
            <option value="">Tampil & tersembunyi</option>
            <option value="1" @selected(request('tampil') === '1')>Tampil</option>
            <option value="0" @selected(request('tampil') === '0')>Disembunyikan</option>
        </select>
        <label for="rating" class="sr-only">Rating</label>
        <select id="rating" name="rating" class="field" onchange="this.form.submit()">
            <option value="">Semua rating</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((int) request('rating') === $i)>{{ $i }} bintang</option>
            @endfor
        </select>
    </form>

    @if ($reviews->isEmpty())
        <div class="card"><x-empty-state icon="message-square-quote" title="Belum ada ulasan" description="Ulasan dari penghuni akan muncul di sini." /></div>
    @else
        <ul class="space-y-3">
            @foreach ($reviews as $review)
                <li @class(['card p-5', 'bg-kapur-50' => ! $review->is_published])>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <x-avatar :user="$review->user" size="sm" />
                            <div>
                                <p class="text-sm font-semibold">{{ $review->user->name }}</p>
                                <p class="text-xs text-ink-500">{{ $review->property->name }} · {{ tanggal($review->created_at) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="num inline-flex items-center gap-1 text-sm font-semibold"><x-lucide-star class="h-4 w-4 fill-kuningan-500 text-kuningan-500" stroke-width="1.5" aria-hidden="true" />{{ $review->rating }}</span>
                            @if ($review->is_published)
                                <x-status-badge tone="success" icon="eye" label="Tampil" size="sm" />
                            @else
                                <x-status-badge tone="neutral" icon="eye-off" label="Disembunyikan" size="sm" />
                            @endif
                        </div>
                    </div>
                    <p @class(['mt-3 text-sm leading-relaxed', 'text-ink-700' => $review->is_published, 'text-ink-500 line-through decoration-ink-300' => ! $review->is_published])>{{ $review->comment }}</p>
                    <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}" class="mt-3 flex justify-end">
                        @csrf @method('PATCH')
                        <x-button type="submit" size="sm" variant="secondary" :icon="$review->is_published ? 'eye-off' : 'eye'">
                            {{ $review->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                        </x-button>
                    </form>
                </li>
            @endforeach
        </ul>
        <x-pagination :paginator="$reviews" />
    @endif
</x-layouts.admin>
