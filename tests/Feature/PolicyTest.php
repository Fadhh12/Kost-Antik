<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_is_limited_to_assigned_properties(): void
    {
        $manager = User::factory()->manager()->create();
        $own = Room::factory()->for(Property::factory()->state(['manager_id' => $manager->id]))->create();
        $other = Room::factory()->create();

        $this->assertTrue($manager->can('update', $own));
        $this->assertFalse($manager->can('update', $other));
        $this->assertTrue($manager->can('update', $own->property));
        $this->assertFalse($manager->can('manage', $own->property));
        $this->assertFalse($manager->can('create', Property::class));
    }

    public function test_owner_can_do_everything(): void
    {
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();

        $this->assertTrue($owner->can('update', $room));
        $this->assertTrue($owner->can('manage', $room->property));
        $this->assertTrue($owner->can('create', Property::class));
    }

    public function test_tenant_only_sees_own_lease(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $stranger = User::factory()->tenant()->create();
        $lease = app(LeaseService::class)->createManual($tenant, Room::factory()->create(), today(), 1, $owner);

        $this->assertTrue($tenant->can('view', $lease));
        $this->assertFalse($stranger->can('view', $lease));
        $this->assertTrue($tenant->can('pay', $lease->invoices->first()));
        $this->assertFalse($stranger->can('pay', $lease->invoices->first()));
    }
}
