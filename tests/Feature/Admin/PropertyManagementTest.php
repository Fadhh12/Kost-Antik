<?php

namespace Tests\Feature\Admin;

use App\Enums\RoomStatus;
use App\Models\Facility;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kost Dahlia',
            'address' => 'Jl. Dahlia No. 5',
            'city' => 'Bekasi',
            'gender_target' => 'female',
            'status' => 'active',
            'latitude' => -6.25,
            'longitude' => 107.01,
        ], $overrides);
    }

    public function test_owner_creates_property_with_photos_and_facilities(): void
    {
        Storage::fake('public');
        $owner = User::factory()->owner()->create();
        $wifi = Facility::factory()->create(['type' => 'shared']);

        $this->actingAs($owner)->post('/admin/properties', $this->payload([
            'facilities' => [$wifi->id],
            'images' => [UploadedFile::fake()->image('depan.jpg'), UploadedFile::fake()->image('kamar.jpg')],
        ]))->assertSessionHasNoErrors();

        $property = Property::firstWhere('name', 'Kost Dahlia');
        $this->assertSame('kost-dahlia', $property->slug);
        $this->assertCount(2, $property->images);
        $this->assertTrue($property->images->first()->is_cover);
        $this->assertTrue($property->facilities->contains($wifi));
    }

    public function test_manager_cannot_create_property(): void
    {
        $this->actingAs(User::factory()->manager()->create())
            ->post('/admin/properties', $this->payload())
            ->assertForbidden();
    }

    public function test_manager_only_updates_basic_info_of_own_property(): void
    {
        $manager = User::factory()->manager()->create();
        $own = Property::factory()->create(['manager_id' => $manager->id, 'name' => 'Nama Asli']);
        $other = Property::factory()->create();

        $this->actingAs($manager)->put("/admin/properties/{$own->slug}", $this->payload([
            'name' => 'Nama Diubah',
            'description' => 'Deskripsi baru dari pengelola.',
        ]))->assertSessionHasNoErrors();

        $own->refresh();
        $this->assertSame('Nama Asli', $own->name);
        $this->assertSame('Deskripsi baru dari pengelola.', $own->description);

        $this->actingAs($manager)->get("/admin/properties/{$other->slug}")->assertForbidden();
        $this->actingAs($manager)->get('/admin/properties')->assertSee($own->name)->assertDontSee($other->name);
    }

    public function test_property_with_lease_history_cannot_be_deleted(): void
    {
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();
        app(LeaseService::class)->createManual(User::factory()->tenant()->create(), $room, today(), 1, $owner);

        $this->actingAs($owner)->delete("/admin/properties/{$room->property->slug}")->assertSessionHas('error');
        $this->assertNotSoftDeleted($room->property);
    }

    public function test_room_crud_and_status_rules(): void
    {
        $manager = User::factory()->manager()->create();
        $property = Property::factory()->create(['manager_id' => $manager->id]);

        $this->actingAs($manager)->post("/admin/properties/{$property->slug}/rooms", [
            'code' => '3A', 'floor' => 3, 'monthly_price' => 1_100_000, 'capacity' => 1, 'status' => 'available',
        ])->assertSessionHasNoErrors();

        $room = $property->rooms()->firstWhere('code', '3A');

        // Kode unik per gedung.
        $this->actingAs($manager)->post("/admin/properties/{$property->slug}/rooms", [
            'code' => '3A', 'monthly_price' => 900_000, 'capacity' => 1, 'status' => 'available',
        ])->assertSessionHasErrors('code');

        // Status occupied tidak bisa dipilih manual.
        $this->actingAs($manager)->put("/admin/properties/{$property->slug}/rooms/{$room->id}", [
            'code' => '3A', 'monthly_price' => 1_100_000, 'capacity' => 1, 'status' => 'occupied',
        ])->assertSessionHasErrors('status');

        $this->actingAs($manager)->put("/admin/properties/{$property->slug}/rooms/{$room->id}", [
            'code' => '3A', 'monthly_price' => 1_200_000, 'capacity' => 1, 'status' => 'maintenance',
        ])->assertSessionHasNoErrors();
        $this->assertSame(RoomStatus::Maintenance, $room->fresh()->status);

        $this->actingAs($manager)->delete("/admin/properties/{$property->slug}/rooms/{$room->id}")->assertSessionHasNoErrors();
        $this->assertModelMissing($room);
    }

    public function test_manager_cannot_touch_rooms_of_other_buildings(): void
    {
        $manager = User::factory()->manager()->create();
        $room = Room::factory()->create();

        $this->actingAs($manager)->put("/admin/properties/{$room->property->slug}/rooms/{$room->id}", [
            'code' => 'X', 'monthly_price' => 1_000_000, 'capacity' => 1, 'status' => 'available',
        ])->assertForbidden();
    }

    public function test_occupied_room_status_is_kept_on_update(): void
    {
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();
        app(LeaseService::class)->createManual(User::factory()->tenant()->create(), $room, today(), 1, $owner);

        $this->actingAs($owner)->put("/admin/properties/{$room->property->slug}/rooms/{$room->id}", [
            'code' => $room->code, 'monthly_price' => 1_500_000, 'capacity' => 1, 'status' => 'available',
        ])->assertSessionHasNoErrors();

        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
    }

    public function test_admin_pages_render(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Room::factory()->create()->property;

        foreach (['kamar', 'info', 'foto', 'ulasan'] as $tab) {
            $this->actingAs($owner)->get("/admin/properties/{$property->slug}?tab={$tab}")->assertOk();
        }
        $this->actingAs($owner)->get('/admin/properties/create')->assertOk();
        $this->actingAs($owner)->get("/admin/properties/{$property->slug}/edit")->assertOk();
    }
}
