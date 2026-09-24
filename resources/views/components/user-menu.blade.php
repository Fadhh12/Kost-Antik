{{-- Dropdown akun di pojok kanan atas (dipakai layout penyewa & admin). --}}
@php $user = auth()->user(); @endphp

<x-dropdown align="right" width="56">
    <x-slot name="trigger">
        <button type="button" class="flex items-center gap-2.5 rounded-lg py-1 pl-1 pr-2 text-left transition hover:bg-kapur-100" aria-haspopup="menu">
            <x-avatar :user="$user" size="sm" />
            <span class="hidden min-w-0 sm:block">
                <span class="block max-w-[10rem] truncate text-sm font-semibold text-ink-900">{{ $user->name }}</span>
                <span class="block text-xs text-ink-500">{{ $user->roleLabel() }}</span>
            </span>
            <x-lucide-chevron-down class="hidden h-4 w-4 text-ink-400 sm:block" stroke-width="2" aria-hidden="true" />
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="border-b border-kapur-100 px-3.5 pb-2.5 pt-1.5 sm:hidden">
            <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
            <p class="truncate text-xs text-ink-500">{{ $user->email }}</p>
        </div>
        <x-dropdown-link :href="route('profile.edit')" icon="user-round">Profil saya</x-dropdown-link>
        <x-dropdown-link :href="route('home')" icon="house">Halaman utama</x-dropdown-link>
        <form method="POST" action="{{ route('logout') }}" class="border-t border-kapur-100 pt-1.5 mt-1.5" data-no-lock>
            @csrf
            <x-dropdown-link as="button" icon="log-out">Keluar</x-dropdown-link>
        </form>
    </x-slot>
</x-dropdown>
