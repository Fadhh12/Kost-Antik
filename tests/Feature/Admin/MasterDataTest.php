<?php

namespace Tests\Feature\Admin;

use App\Models\Facility;
use App\Models\Instance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_manages_facilities(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get('/admin/facilities')->assertOk();
        $this->actingAs($owner)->post('/admin/facilities', ['name' => 'Water heater', 'icon' => 'heater', 'type' => 'room'])->assertSessionHasNoErrors();

        $facility = Facility::firstWhere('name', 'Water heater');
        $this->actingAs($owner)->put("/admin/facilities/{$facility->id}", ['name' => 'Pemanas air', 'icon' => 'heater', 'type' => 'room'])->assertSessionHasNoErrors();
        $this->assertSame('Pemanas air', $facility->fresh()->name);

        $this->actingAs($owner)->delete("/admin/facilities/{$facility->id}");
        $this->assertModelMissing($facility);
    }

    public function test_unknown_icon_is_rejected(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post('/admin/facilities', ['name' => 'Aneh', 'icon' => 'tidak-ada-ikon', 'type' => 'room'])
            ->assertSessionHasErrors('icon');
    }

    public function test_owner_manages_instances(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/admin/instances', ['name' => 'Universitas Mercu Buana Bekasi', 'type' => 'campus'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('instances', ['name' => 'Universitas Mercu Buana Bekasi']);

        $this->actingAs($owner)->post('/admin/instances', ['name' => 'Universitas Mercu Buana Bekasi', 'type' => 'campus'])->assertSessionHasErrors('name');
    }

    public function test_manager_cannot_open_master_data(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get('/admin/facilities')->assertForbidden();
        $this->actingAs($manager)->post('/admin/instances', ['name' => 'X', 'type' => 'campus'])->assertForbidden();
        $this->assertSame(0, Instance::count());
    }
}
