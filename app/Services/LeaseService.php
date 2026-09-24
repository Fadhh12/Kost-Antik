<?php

namespace App\Services;

use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\BookingRequest;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaseService
{
    public function __construct(
        private InvoiceService $invoices,
        private CodeGenerator $codes,
        private RentalEligibility $eligibility,
    ) {}

    /**
     * Inti pembuatan kontrak. Pemanggil wajib berada di dalam transaksi
     * dan sudah mengunci baris kamar (lockForUpdate).
     */
    public function create(
        User $tenant,
        Room $room,
        CarbonInterface $startDate,
        int $durationMonths,
        User $actor,
        ?BookingRequest $booking = null,
    ): Lease {
        $start = Carbon::parse($startDate)->startOfDay();

        $lease = Lease::create([
            'code' => $this->codes->leaseCode($start),
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'booking_request_id' => $booking?->id,
            'start_date' => $start,
            // FR-LEASE-02: mulai 10 Jan, 3 bulan -> selesai 9 Apr.
            'end_date' => $start->copy()->addMonthsNoOverflow($durationMonths)->subDay(),
            'duration_months' => $durationMonths,
            'monthly_price' => $room->monthly_price, // BR-04 snapshot
            'total_amount' => $room->monthly_price * $durationMonths, // FR-LEASE-04
            'paid_amount' => 0,
            'status' => LeaseStatus::Active,
            'created_by' => $actor->id,
        ]);

        $this->invoices->generateForLease($lease);

        $room->update(['status' => RoomStatus::Occupied]); // BR-03

        activity('lease')
            ->performedOn($lease)
            ->causedBy($actor)
            ->event('created')
            ->withProperties(['room' => $room->code, 'tenant' => $tenant->name, 'booking_id' => $booking?->id])
            ->log('Kontrak dibuat');

        return $lease;
    }

    /**
     * FR-LEASE-01: kontrak walk-in oleh pengelola/owner.
     */
    public function createManual(User $tenant, Room $room, CarbonInterface $startDate, int $durationMonths, User $actor): Lease
    {
        return DB::transaction(function () use ($tenant, $room, $startDate, $durationMonths, $actor) {
            $room = Room::whereKey($room->id)->lockForUpdate()->firstOrFail();
            $this->eligibility->assert($tenant, $room, checkPendingBooking: false);

            return $this->create($tenant, $room, $startDate, $durationMonths, $actor);
        });
    }

    /**
     * FR-LEASE-06/07: akhiri kontrak lebih awal.
     */
    public function terminate(Lease $lease, CarbonInterface $terminatedAt, string $reason, User $actor): Lease
    {
        return DB::transaction(function () use ($lease, $terminatedAt, $reason, $actor) {
            $lease = Lease::whereKey($lease->id)->lockForUpdate()->firstOrFail();

            if (! $lease->isActive()) {
                throw new BusinessRuleException('Hanya kontrak aktif yang bisa diakhiri.');
            }

            $date = Carbon::parse($terminatedAt)->startOfDay();
            if ($date->lt($lease->start_date) || $date->gt($lease->end_date)) {
                throw new BusinessRuleException('Tanggal berakhir harus di antara tanggal mulai dan selesai kontrak.');
            }

            $lease->update([
                'status' => LeaseStatus::Terminated,
                'terminated_at' => $date,
                'termination_reason' => $reason,
            ]);

            $voided = $this->invoices->voidAfter($lease, $date);
            $this->releaseRoom($lease->room);

            activity('lease')
                ->performedOn($lease)
                ->causedBy($actor)
                ->event('terminated')
                ->withProperties(['reason' => $reason, 'terminated_at' => $date->toDateString(), 'voided_invoices' => $voided])
                ->log('Kontrak diakhiri lebih awal');

            return $lease;
        });
    }

    /**
     * FR-LEASE-06: kontrak aktif yang melewati end_date menjadi selesai.
     */
    public function completeExpired(): int
    {
        $count = 0;

        Lease::query()
            ->where('status', LeaseStatus::Active)
            ->whereDate('end_date', '<', today())
            ->with('room')
            ->each(function (Lease $lease) use (&$count) {
                DB::transaction(function () use ($lease) {
                    $lease->update(['status' => LeaseStatus::Completed]);
                    $this->releaseRoom($lease->room);

                    activity('lease')->performedOn($lease)->event('completed')->log('Kontrak selesai otomatis');
                });
                $count++;
            });

        return $count;
    }

    /**
     * FR-LEASE-07: kamar kembali tersedia, kecuali sedang perbaikan atau masih punya kontrak aktif lain.
     */
    private function releaseRoom(Room $room): void
    {
        $room->refresh();

        if ($room->status !== RoomStatus::Occupied) {
            return;
        }

        if ($room->leases()->where('status', LeaseStatus::Active)->exists()) {
            return;
        }

        $room->update(['status' => RoomStatus::Available]);
    }
}
