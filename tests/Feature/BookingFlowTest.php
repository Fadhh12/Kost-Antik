<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\GenderTarget;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function book(User $tenant, Room $room, array $overrides = [])
    {
        return $this->actingAs($tenant)->post('/app/bookings', array_merge([
            'room_id' => $room->id,
            'start_date' => today()->addDays(5)->toDateString(),
            'duration_months' => 3,
            'note' => 'Saya pindah awal bulan.',
            'agree_rules' => '1',
        ], $overrides));
    }

    public function test_eligible_tenant_can_book(): void
    {
        $tenant = User::factory()->tenant()->create();
        $room = Room::factory()->create();

        $this->book($tenant, $room)->assertRedirect(route('app.bookings.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', ['user_id' => $tenant->id, 'room_id' => $room->id, 'status' => 'pending']);
        $this->actingAs($tenant)->get('/app/bookings')->assertOk()->assertSee($room->property->name);
    }

    public function test_pending_account_cannot_book(): void
    {
        $this->book(User::factory()->tenant()->pending()->create(), Room::factory()->create())->assertSessionHas('error');
        $this->assertSame(0, BookingRequest::count());
    }

    public function test_gender_mismatch_cannot_book(): void
    {
        $room = Room::factory()->for(Property::factory()->forGender(GenderTarget::Female))->create();

        $this->book(User::factory()->tenant()->male()->create(), $room)->assertSessionHas('error', 'Kost ini khusus putri.');
    }

    public function test_tenant_with_active_lease_or_pending_booking_cannot_book(): void
    {
        $tenant = User::factory()->tenant()->create();
        $this->book($tenant, Room::factory()->create())->assertSessionHas('success');
        $this->book($tenant, Room::factory()->create())->assertSessionHas('error');

        $leased = User::factory()->tenant()->create();
        app(LeaseService::class)->createManual($leased, Room::factory()->create(), today(), 1, User::factory()->owner()->create());
        $this->book($leased, Room::factory()->create())->assertSessionHas('error');
    }

    public function test_full_or_maintenance_room_cannot_be_booked(): void
    {
        $this->book(User::factory()->tenant()->create(), Room::factory()->occupied()->create())->assertSessionHas('error');
        $this->book(User::factory()->tenant()->create(), Room::factory()->maintenance()->create())->assertSessionHas('error');
    }

    public function test_start_date_and_duration_are_validated(): void
    {
        $tenant = User::factory()->tenant()->create();
        $room = Room::factory()->create();

        $this->book($tenant, $room, ['start_date' => today()->subDay()->toDateString()])->assertSessionHasErrors('start_date');
        $this->book($tenant, $room, ['start_date' => today()->addDays(61)->toDateString()])->assertSessionHasErrors('start_date');
        $this->book($tenant, $room, ['duration_months' => 2])->assertSessionHasErrors('duration_months');
        $this->book($tenant, $room, ['agree_rules' => null])->assertSessionHasErrors('agree_rules');
    }

    public function test_tenant_can_cancel_pending_booking_only(): void
    {
        $tenant = User::factory()->tenant()->create();
        $this->book($tenant, Room::factory()->create());
        $booking = BookingRequest::first();

        $this->actingAs(User::factory()->tenant()->create())->patch("/app/bookings/{$booking->id}/cancel")->assertForbidden();
        $this->actingAs($tenant)->patch("/app/bookings/{$booking->id}/cancel")->assertSessionHas('success');
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_approval_creates_lease_invoices_and_rejects_competing_bookings(): void
    {
        $manager = User::factory()->manager()->create();
        $room = Room::factory()->for(Property::factory()->state(['manager_id' => $manager->id]))->create(['monthly_price' => 1_000_000]);

        $first = User::factory()->tenant()->create();
        $second = User::factory()->tenant()->create();
        $this->book($first, $room, ['duration_months' => 6]);
        $this->book($second, $room);
        [$winner, $loser] = BookingRequest::orderBy('id')->get();

        $this->actingAs($manager)->patch("/admin/bookings/{$winner->id}/approve")->assertSessionHas('success');

        $lease = $winner->fresh()->lease;
        $this->assertSame(LeaseStatus::Active, $lease->status);
        $this->assertSame(6, $lease->invoices()->count());
        $this->assertSame(6_000_000, $lease->total_amount);
        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
        $this->assertSame(BookingStatus::Approved, $winner->fresh()->status);
        $this->assertSame(BookingStatus::Rejected, $loser->fresh()->status);
        $this->assertSame(BookingService::TAKEN_REASON, $loser->fresh()->reject_reason);
    }

    public function test_reject_requires_reason(): void
    {
        $owner = User::factory()->owner()->create();
        $this->book(User::factory()->tenant()->create(), Room::factory()->create());
        $booking = BookingRequest::first();

        $this->actingAs($owner)->patch("/admin/bookings/{$booking->id}/reject", ['reason' => 'pendek'])->assertSessionHasErrors('reason');
        $this->actingAs($owner)->patch("/admin/bookings/{$booking->id}/reject", ['reason' => 'Kamar akan direnovasi bulan depan.'])->assertSessionHas('success');
        $this->assertSame(BookingStatus::Rejected, $booking->fresh()->status);
    }

    public function test_manager_cannot_process_other_buildings(): void
    {
        $this->book(User::factory()->tenant()->create(), Room::factory()->create());
        $booking = BookingRequest::first();
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->patch("/admin/bookings/{$booking->id}/approve")->assertForbidden();
        $this->actingAs($manager)->get('/admin/bookings')->assertOk()->assertDontSee($booking->user->name);
    }

    public function test_stale_bookings_expire(): void
    {
        $this->book(User::factory()->tenant()->create(), Room::factory()->create());
        Carbon::setTestNow(now()->addDays(8));

        $this->artisan('bookings:expire-stale')->assertSuccessful();

        $this->assertSame(BookingStatus::Expired, BookingRequest::first()->status);
    }
}
