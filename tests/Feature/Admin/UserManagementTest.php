<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserStatus;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_approves_and_rejects_pending_tenants(): void
    {
        $owner = User::factory()->owner()->create();
        $a = User::factory()->tenant()->pending()->create();
        $b = User::factory()->tenant()->pending()->create();

        $this->actingAs($owner)->get('/admin/users?tab=pending')->assertOk()->assertSee($a->name);

        $this->actingAs($owner)->patch("/admin/users/{$a->id}/approve")->assertSessionHas('success');
        $this->actingAs($owner)->patch("/admin/users/{$b->id}/reject", ['reason' => 'Nomor HP tidak bisa dihubungi.'])->assertSessionHas('success');

        $this->assertSame(UserStatus::Accepted, $a->fresh()->status);
        $this->assertSame(UserStatus::Rejected, $b->fresh()->status);
        $this->assertSame('Nomor HP tidak bisa dihubungi.', $b->fresh()->status_reason);
    }

    public function test_owner_creates_manager_and_assigns_property(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create();

        $this->actingAs($owner)->post('/admin/users/managers', [
            'name' => 'Asep Sunandar',
            'email' => 'asep@example.com',
            'phone' => '081299990000',
            'gender' => 'male',
            'password' => 'rahasia123',
            'properties' => [$property->id],
        ])->assertSessionHasNoErrors();

        $manager = User::firstWhere('email', 'asep@example.com');
        $this->assertTrue($manager->isManager());
        $this->assertTrue($manager->isAccepted());
        $this->assertSame($manager->id, $property->fresh()->manager_id);
    }

    public function test_deactivation_blocked_with_active_lease_and_cancels_pending_bookings(): void
    {
        $owner = User::factory()->owner()->create();
        $leased = User::factory()->tenant()->create();
        app(LeaseService::class)->createManual($leased, Room::factory()->create(), today(), 1, $owner);

        $this->actingAs($owner)->patch("/admin/users/{$leased->id}/deactivate", ['reason' => 'Sudah tidak tinggal di kost.'])->assertSessionHas('error');

        $booker = User::factory()->tenant()->create();
        $booking = BookingRequest::factory()->for($booker)->create();
        $this->actingAs($owner)->patch("/admin/users/{$booker->id}/deactivate", ['reason' => 'Sudah tidak tinggal di kost.'])->assertSessionHas('success');

        $this->assertSame(UserStatus::Inactive, $booker->fresh()->status);
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_manager_sees_only_tenants_of_own_buildings(): void
    {
        $manager = User::factory()->manager()->create();
        $ownRoom = Room::factory()->for(Property::factory()->state(['manager_id' => $manager->id]))->create();
        $mine = User::factory()->tenant()->create();
        $stranger = User::factory()->tenant()->create();
        BookingRequest::factory()->for($mine)->for($ownRoom)->create();
        BookingRequest::factory()->for($stranger)->create();

        $this->actingAs($manager)->get('/admin/users')->assertOk()->assertSee($mine->name)->assertDontSee($stranger->name);
        $this->actingAs($manager)->get("/admin/users/{$mine->id}")->assertOk();
        $this->actingAs($manager)->get("/admin/users/{$stranger->id}")->assertForbidden();
        $this->actingAs($manager)->patch("/admin/users/{$mine->id}/deactivate", ['reason' => 'Mencoba tanpa izin.'])->assertForbidden();
    }
}
