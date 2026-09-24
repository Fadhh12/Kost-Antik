@if ($reviews->isEmpty())
    <div class="card"><x-empty-state icon="message-square-quote" title="Belum ada ulasan" description="Ulasan muncul setelah penghuni tinggal minimal 30 hari." /></div>
@else
    <div class="mb-4 flex items-center gap-3">
        <x-rating :value="$property->rating_avg" :count="$property->reviews_count" size="lg" />
        @if (Route::has('admin.reviews.index') && auth()->user()->isOwner())
            <a href="{{ route('admin.reviews.index', ['property' => $property->id]) }}" class="link text-sm">Moderasi ulasan</a>
        @endif
    </div>
    <ul class="space-y-3">
        @foreach ($reviews as $review)
            <li class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <x-avatar :user="$review->user" size="sm" />
                        <div>
                            <p class="text-sm font-semibold">{{ $review->user->name }}</p>
                            <p class="text-xs text-ink-500">{{ tanggal($review->created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="num inline-flex items-center gap-1 text-sm font-semibold"><x-lucide-star class="h-4 w-4 fill-kuningan-500 text-kuningan-500" stroke-width="1.5" aria-hidden="true" />{{ $review->rating }}</span>
                        @unless ($review->is_published)
                            <x-status-badge tone="neutral" icon="eye-off" label="Disembunyikan" size="sm" />
                        @endunless
                    </div>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-ink-700">{{ $review->comment }}</p>
            </li>
        @endforeach
    </ul>
    <x-pagination :paginator="$reviews" />
@endif
