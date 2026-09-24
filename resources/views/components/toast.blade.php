{{--
    Toast global. Controller cukup: ->with('success', '...') atau ->with('error', '...').
    Bisa juga dari JS: window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } })).
--}}
@php
    $initial = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'info', 'message' => session('info')],
    ])->filter(fn ($t) => filled($t['message']))->values();
@endphp

<div x-data="{
        toasts: [],
        add(t) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, ...t });
            setTimeout(() => this.remove(id), t.type === 'error' ? 7000 : 4500);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id) },
    }"
    x-init="@js($initial).forEach(t => add(t))"
    x-on:toast.window="add($event.detail)"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-toast flex flex-col items-center gap-2 p-4 sm:bottom-auto sm:left-auto sm:top-0 sm:items-end sm:p-6"
    aria-live="polite" role="status">
    <template x-for="t in toasts" :key="t.id">
        <div x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="ease-in duration-150" x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border bg-white p-3.5 pr-2 shadow-lift"
            :class="{ 'border-success-line': t.type === 'success', 'border-danger-line': t.type === 'error', 'border-tegel-100': t.type === 'info' }">
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                :class="{ 'bg-success-soft text-success': t.type === 'success', 'bg-danger-soft text-danger': t.type === 'error', 'bg-tegel-50 text-tegel-700': t.type === 'info' }">
                <x-lucide-check class="h-3.5 w-3.5" stroke-width="2.5" x-show="t.type === 'success'" aria-hidden="true" />
                <x-lucide-x class="h-3.5 w-3.5" stroke-width="2.5" x-show="t.type === 'error'" aria-hidden="true" />
                <x-lucide-info class="h-3.5 w-3.5" stroke-width="2.5" x-show="t.type === 'info'" aria-hidden="true" />
            </span>
            <p class="flex-1 pt-0.5 text-sm font-medium text-ink-900" x-text="t.message"></p>
            <button type="button" x-on:click="remove(t.id)" class="rounded-md p-1 text-ink-400 hover:bg-kapur-100 hover:text-ink-900">
                <span class="sr-only">Tutup notifikasi</span>
                <x-lucide-x class="h-4 w-4" stroke-width="1.75" aria-hidden="true" />
            </button>
        </div>
    </template>
</div>
