<x-layouts.admin heading="Dashboard" subheading="Ringkasan gedung yang kamu kelola.">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Gedung" :value="$properties->count()" icon="building-2" />
        <x-stat-card label="Kamar" :value="$properties->sum('rooms_count')" icon="bed-double" />
        <x-stat-card label="Terisi" :value="$properties->sum('occupied_rooms_count')" icon="door-closed" />
        <x-stat-card label="Kosong" :value="$properties->sum('available_rooms_count')" icon="door-open" />
    </div>
</x-layouts.admin>
