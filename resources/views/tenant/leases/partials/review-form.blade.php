{{-- Form ulasan (buat/ubah). $review null = ulasan baru. --}}
<form method="POST" action="{{ $review ? route('app.reviews.update', $review) : route('app.leases.review', $lease) }}" class="mt-4 space-y-4"
    x-data="{ rating: {{ (int) old('rating', $review?->rating ?? 0) }}, hover: 0 }">
    @csrf
    @if ($review) @method('PUT') @endif

    <fieldset>
        <legend class="label">Rating <span class="text-danger" aria-hidden="true">*</span></legend>
        <div class="flex items-center gap-1" x-on:mouseleave="hover = 0">
            @for ($i = 1; $i <= 5; $i++)
                <label class="cursor-pointer rounded-md p-0.5 focus-within:ring-2 focus-within:ring-kuningan-500" x-on:mouseenter="hover = {{ $i }}">
                    <input type="radio" name="rating" value="{{ $i }}" x-model.number="rating" class="sr-only" required>
                    <span class="sr-only">{{ $i }} bintang</span>
                    <x-lucide-star class="h-8 w-8 transition" stroke-width="1.5"
                        ::class="(hover || rating) >= {{ $i }} ? 'fill-kuningan-500 text-kuningan-500' : 'text-ink-300'" aria-hidden="true" />
                </label>
            @endfor
            <span class="ml-2 text-sm text-ink-500" x-text="['', 'Kurang', 'Cukup', 'Baik', 'Sangat baik', 'Luar biasa'][hover || rating]"></span>
        </div>
        <x-input-error for="rating" />
    </fieldset>

    <x-textarea name="comment" label="Ceritakan pengalamanmu" :value="$review?->comment" rows="4" maxlength="500" required minlength="10"
        hint="10-500 karakter. Misal soal kebersihan, keamanan, atau respons pengelola." />

    <div class="flex justify-end">
        <x-button type="submit" icon="send">{{ $review ? 'Simpan ulasan' : 'Kirim ulasan' }}</x-button>
    </div>
</form>
