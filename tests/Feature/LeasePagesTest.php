<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeasePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_creates_walk_in_lease_with_first_month_cash(): void
    {
        $manager = User::factory()->manager()->create();
        $room = Room::factory()->for(Property::factory()->state(['manager_id' => $manager->id]))->create(['monthly_price' => 900_000]);
        $tenant = User::factory()->tenant()->create();

        $this->actingAs($manager)->get('/admin/leases/create')->assertOk()->assertSee($tenant->name);

        $this->actingAs($manager)->post('/admin/leases', [
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => today()->subDays(10)->toDateString(),
            'duration_months' => 3,
            'pay_first_cash' => '1',
        ])->assertSessionHasNoErrors();

        $lease = Lease::firstWhere('user_id', $tenant->id);
        $this->assertSame(3, $lease->invoices()->count());
        $this->assertSame(InvoiceStatus::Paid, $lease->invoices()->orderBy('sequence')->first()->status);
        $this->assertSame(900_000, $lease->paid_amount);
    }

    public function test_walk_in_start_date_limited_to_30_days_back(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/admin/leases', [
            'user_id' => User::factory()->tenant()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'start_date' => today()->subDays(31)->toDateString(),
            'duration_months' => 1,
        ])->assertSessionHasErrors('start_date');
    }

    public function test_manager_cannot_create_lease_in_other_building(): void
    {
        $this->actingAs(User::factory()->manager()->create())->post('/admin/leases', [
            'user_id' => User::factory()->tenant()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'start_date' => today()->toDateString(),
            'duration_months' => 1,
        ])->assertForbidden();
    }

    public function test_terminate_via_http_requires_reason_and_valid_date(): void
    {
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), $room, today(), 3, $owner);

        $this->actingAs($owner)->patch("/admin/leases/{$lease->id}/terminate", ['terminated_at' => today()->toDateString(), 'reason' => 'x'])
            ->assertSessionHasErrors('reason');
        $this->actingAs($owner)->patch("/admin/leases/{$lease->id}/terminate", ['terminated_at' => today()->addYear()->toDateString(), 'reason' => 'Pindah kerja ke Surabaya.'])
            ->assertSessionHasErrors('terminated_at');
        $this->actingAs($owner)->patch("/admin/leases/{$lease->id}/terminate", ['terminated_at' => today()->toDateString(), 'reason' => 'Pindah kerja ke Surabaya.'])
            ->assertSessionHas('success');

        $this->assertSame(LeaseStatus::Terminated, $lease->fresh()->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
    }

    public function test_admin_pages_render(): void
    {
        $owner = User::factory()->owner()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), Room::factory()->create(), today(), 2, $owner);

        $this->actingAs($owner)->get('/admin/leases')->assertOk()->assertSee($lease->code);
        $this->actingAs($owner)->get("/admin/leases/{$lease->id}")->assertOk()->assertSee('Jadwal tagihan');
        $this->actingAs($owner)->get('/admin/invoices')->assertOk()->assertSee($lease->invoices->first()->number);
        $this->actingAs($owner)->get('/admin/invoices?status=overdue&periode=2026-01')->assertOk();
    }

    public function test_tenant_sees_only_own_lease_and_invoices(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $other = User::factory()->tenant()->create();
        $lease = app(LeaseService::class)->createManual($tenant, Room::factory()->create(), today(), 2, $owner);

        $this->actingAs($tenant)->get('/app/leases')->assertOk()->assertSee($lease->code);
        $this->actingAs($tenant)->get("/app/leases/{$lease->id}")->assertOk();
        $this->actingAs($tenant)->get('/app/invoices')->assertOk()->assertSee($lease->invoices->first()->number);

        $this->actingAs($other)->get("/app/leases/{$lease->id}")->assertForbidden();
        $this->actingAs($other)->get('/app/invoices')->assertOk()->assertDontSee($lease->invoices->first()->number);
    }
}
