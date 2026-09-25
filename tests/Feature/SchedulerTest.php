<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_commands_are_scheduled_daily(): void
    {
        $events = collect(app(Schedule::class)->events())->mapWithKeys(fn ($e) => [
            trim(str_replace(["'", '"'], '', substr($e->command, strpos($e->command, 'artisan') + 7))) => $e->expression,
        ]);

        $this->assertSame('5 0 * * *', $events['invoices:mark-overdue']);
        $this->assertSame('10 0 * * *', $events['leases:complete-expired']);
        $this->assertSame('15 0 * * *', $events['bookings:expire-stale']);
        $this->assertSame('0 2 * * *', $events['backup:run']);
    }

    public function test_overdue_and_completion_commands(): void
    {
        Carbon::setTestNow('2026-03-01');
        $room = Room::factory()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), $room, today(), 1, User::factory()->owner()->create());

        Carbon::setTestNow('2026-03-05');
        $this->artisan('invoices:mark-overdue')->expectsOutputToContain('1 tagihan')->assertSuccessful();
        $this->assertSame(InvoiceStatus::Overdue, $lease->invoices()->first()->status);

        Carbon::setTestNow('2026-04-01');
        $this->artisan('leases:complete-expired')->expectsOutputToContain('1 kontrak')->assertSuccessful();
        $this->assertSame(LeaseStatus::Completed, $lease->fresh()->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);

        // Kontrak selesai dengan tunggakan tetap ditandai (FR-LEASE-08).
        $this->actingAs(User::factory()->owner()->create())->get('/admin/leases?tunggakan=1')->assertSee($lease->code);
    }

    public function test_maintenance_room_stays_in_maintenance_after_lease_ends(): void
    {
        Carbon::setTestNow('2026-03-01');
        $room = Room::factory()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), $room, today(), 1, User::factory()->owner()->create());
        $room->forceFill(['status' => RoomStatus::Maintenance])->save();

        Carbon::setTestNow('2026-04-02');
        $this->artisan('leases:complete-expired')->assertSuccessful();

        $this->assertSame(RoomStatus::Maintenance, $room->fresh()->status);
    }
}
