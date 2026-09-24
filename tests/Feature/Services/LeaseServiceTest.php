<?php

namespace Tests\Feature\Services;

use App\Enums\GenderTarget;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): LeaseService
    {
        return app(LeaseService::class);
    }

    public function test_manual_lease_snapshots_price_and_generates_monthly_invoices(): void
    {
        Carbon::setTestNow('2026-01-05');
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $room = Room::factory()->create(['monthly_price' => 1_250_000]);

        $lease = $this->service()->createManual($tenant, $room, Carbon::parse('2026-01-10'), 3, $owner);

        $this->assertSame('2026-04-09', $lease->end_date->toDateString());
        $this->assertSame(3_750_000, $lease->total_amount);
        $this->assertMatchesRegularExpression('/^KA-LS-202601-\d{4}$/', $lease->code);
        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);

        $invoices = $lease->invoices()->get();
        $this->assertCount(3, $invoices);
        $this->assertSame('2026-02-10', $invoices[1]->period_start->toDateString());
        $this->assertSame('2026-03-09', $invoices[1]->period_end->toDateString());
        $this->assertSame('2026-02-13', $invoices[1]->due_date->toDateString());
        $this->assertTrue($invoices->every(fn ($i) => $i->amount === 1_250_000 && $i->status === InvoiceStatus::Unpaid));

        // BR-04: perubahan harga kamar tidak memengaruhi kontrak berjalan.
        $room->update(['monthly_price' => 2_000_000]);
        $this->assertSame(1_250_000, $lease->fresh()->monthly_price);
    }

    public function test_end_of_month_start_does_not_drift(): void
    {
        $owner = User::factory()->owner()->create();
        $lease = $this->service()->createManual(
            User::factory()->tenant()->create(),
            Room::factory()->create(),
            Carbon::parse('2026-01-31'),
            3,
            $owner,
        );

        $this->assertSame(
            ['2026-01-31', '2026-02-28', '2026-03-31'],
            $lease->invoices()->pluck('period_start')->map->toDateString()->all(),
        );
    }

    public function test_gender_mismatch_is_rejected(): void
    {
        $room = Room::factory()->for(Property::factory()->forGender(GenderTarget::Female))->create();

        $this->expectException(BusinessRuleException::class);
        $this->service()->createManual(User::factory()->tenant()->male()->create(), $room, today(), 1, User::factory()->owner()->create());
    }

    public function test_tenant_with_active_lease_cannot_get_another(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $this->service()->createManual($tenant, Room::factory()->create(), today(), 1, $owner);

        $this->expectException(BusinessRuleException::class);
        $this->service()->createManual($tenant, Room::factory()->create(), today(), 1, $owner);
    }

    public function test_occupied_or_maintenance_room_cannot_be_leased(): void
    {
        $this->expectException(BusinessRuleException::class);
        $this->service()->createManual(User::factory()->tenant()->create(), Room::factory()->maintenance()->create(), today(), 1, User::factory()->owner()->create());
    }

    public function test_terminate_voids_future_invoices_and_frees_room(): void
    {
        Carbon::setTestNow('2026-01-01');
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();
        $lease = $this->service()->createManual(User::factory()->tenant()->create(), $room, Carbon::parse('2026-01-01'), 6, $owner);

        $this->service()->terminate($lease, Carbon::parse('2026-02-15'), 'Pindah kerja ke luar kota.', $owner);

        $lease->refresh();
        $this->assertSame(LeaseStatus::Terminated, $lease->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
        $this->assertSame(2, $lease->invoices()->where('status', '!=', InvoiceStatus::Void)->count());
        $this->assertSame(4, $lease->invoices()->where('status', InvoiceStatus::Void)->count());
    }

    public function test_scheduler_marks_overdue_and_completes_expired_leases(): void
    {
        Carbon::setTestNow('2026-01-01');
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();
        $lease = $this->service()->createManual(User::factory()->tenant()->create(), $room, Carbon::parse('2026-01-01'), 1, $owner);

        Carbon::setTestNow('2026-01-05');
        $this->assertSame(1, app(InvoiceService::class)->markOverdue());

        Carbon::setTestNow('2026-02-01');
        $this->assertSame(1, $this->service()->completeExpired());
        $this->assertSame(LeaseStatus::Completed, $lease->fresh()->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
        $this->assertTrue($lease->fresh()->hasArrears());
    }
}
