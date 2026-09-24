@php
    use App\Enums\RoomStatus;

    $user = auth()->user();
    $manage = $user->can('manage', $property);
    $rooms = $property->rooms;
    $counts = [
        'available' => $rooms->where('status', RoomStatus::Available)->count(),
        'occupied' => $rooms->where('status', RoomStatus::Occupied)->count(),
        'maintenance' => $rooms->where('status', RoomStatus::Maintenance)->count(),
    ];
    $tabs = [
        'kamar' => ['label' => 'Kamar', 'url' => route('admin.properties.show', [$property, 'tab' => 'kamar']), 'count' => $rooms->count()],
        'info' => ['label' => 'Info', 'url' => route('admin.properties.show', [$property, 'tab' => 'info'])],
        'foto' => ['label' => 'Foto', 'url' => route('admin.properties.show', [$property, 'tab' => 'foto']), 'count' => $property->images->count()],
        'ulasan' => ['label' => 'Ulasan', 'url' => route('admin.properties.show', [$property, 'tab' => 'ulasan']), 'count' => $property->reviews_count],
    ];
@endphp

<x-layouts.admin :title="$property->name" :heading="$property->name" :subheading="$property->address.', '.$property->city"
    :breadcrumb="['Gedung' => route('admin.properties.index'), $property->name => null]">
    <x-slot name="actions">
        <x-button :href="route('kost.show', $property)" variant="ghost" icon="external-link" target="_blank">Lihat publik</x-button>
        @can('update', $property)
            <x-button :href="route('admin.properties.edit', $property)" variant="secondary" icon="pencil">Ubah gedung</x-button>
        @endcan
    </x-slot>

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <x-status-badge :status="$property->status" />
        <x-status-badge tone="info" :icon="$property->gender_target->icon()" :label="'Kost '.strtolower($property->gender_target->label())" />
        <span class="text-sm text-ink-500">Pengelola: <span class="font-medium text-ink-900">{{ $property->manager?->name ?? 'belum ditugaskan' }}</span></span>
    </div>

    <x-tabs :tabs="$tabs" :active="$tab" class="mb-6" />

    @if ($tab === 'kamar')
        @include('admin.properties.partials.rooms')
    @elseif ($tab === 'info')
        @include('admin.properties.partials.info')
    @elseif ($tab === 'foto')
        @include('admin.properties.partials.photos')
    @else
        @include('admin.properties.partials.reviews')
    @endif
</x-layouts.admin>
