{{-- Ringkasan error di atas form (prinsip interaksi 4.5). --}}
@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-danger-line bg-danger-soft p-4']) }} role="alert">
        <div class="flex gap-3">
            <x-lucide-circle-alert class="h-5 w-5 shrink-0 text-danger" stroke-width="1.75" aria-hidden="true" />
            <div class="text-sm">
                <p class="font-semibold text-danger">Periksa lagi {{ $errors->count() }} isian berikut:</p>
                <ul class="mt-1.5 list-disc space-y-0.5 pl-4 text-ink-700">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
