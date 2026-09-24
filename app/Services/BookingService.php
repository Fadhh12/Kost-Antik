<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\BookingRequest;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public const TAKEN_REASON = 'Kamar sudah disewa penyewa lain';

    public function __construct(
        private RentalEligibility $eligibility,
        private LeaseService $leases,
    ) {}

    /**
     * F-10 / FR-BOOK-01..03: penyewa mengajukan sewa.
     */
    public function create(User $tenant, Room $room, CarbonInterface $startDate, int $durationMonths, ?string $note): BookingRequest
    {
        return DB::transaction(function () use ($tenant, $room, $startDate, $durationMonths, $note) {
            // Kunci baris user agar dua pengajuan bersamaan tidak lolos BR-01.
            User::whereKey($tenant->id)->lockForUpdate()->first();
            $room = Room::whereKey($room->id)->lockForUpdate()->firstOrFail();

            $this->eligibility->assert($tenant, $room);

            $booking = BookingRequest::create([
                'user_id' => $tenant->id,
                'room_id' => $room->id,
                'start_date' => $startDate,
                'duration_months' => $durationMonths,
                'note' => $note,
            ]);

            activity('booking')->performedOn($booking)->causedBy($tenant)->event('created')
                ->withProperties(['room' => $room->code, 'property' => $room->property->name])
                ->log('Pengajuan sewa dibuat');

            return $booking;
        });
    }

    /**
     * FR-BOOK-04: batalkan selama pending.
     */
    public function cancel(BookingRequest $booking, User $tenant): BookingRequest
    {
        return DB::transaction(function () use ($booking, $tenant) {
            $booking = BookingRequest::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! $booking->isPending()) {
                throw new BusinessRuleException('Pengajuan ini sudah diproses sehingga tidak bisa dibatalkan.');
            }

            $booking->forceFill(['status' => BookingStatus::Cancelled])->save();

            activity('booking')->performedOn($booking)->causedBy($tenant)->event('cancelled')->log('Pengajuan sewa dibatalkan');

            return $booking;
        });
    }

    /**
     * FR-BOOK-03/06: setujui -> kontrak + invoice, kamar terisi, booking lain untuk kamar itu ditolak.
     */
    public function approve(BookingRequest $booking, User $actor): Lease
    {
        return DB::transaction(function () use ($booking, $actor) {
            $booking = BookingRequest::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $room = Room::whereKey($booking->room_id)->lockForUpdate()->firstOrFail();

            if (! $booking->isPending()) {
                throw new BusinessRuleException('Pengajuan ini sudah diproses.');
            }

            // Cek ulang di dalam transaksi (kamar bisa saja sudah terisi).
            $this->eligibility->assert($booking->user, $room, ignoreBookingId: $booking->id);

            // Tanggal mulai yang sudah lewat saat disetujui digeser ke hari ini.
            $start = $booking->start_date->lt(today()) ? today() : $booking->start_date;

            $lease = $this->leases->create($booking->user, $room, $start, $booking->duration_months, $actor, $booking);

            $booking->forceFill([
                'status' => BookingStatus::Approved,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ])->save();

            $others = BookingRequest::where('room_id', $room->id)
                ->where('status', BookingStatus::Pending)
                ->whereKeyNot($booking->id)
                ->update([
                    'status' => BookingStatus::Rejected,
                    'reject_reason' => self::TAKEN_REASON,
                    'reviewed_by' => $actor->id,
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            activity('booking')->performedOn($booking)->causedBy($actor)->event('approved')
                ->withProperties(['lease' => $lease->code, 'auto_rejected' => $others])
                ->log('Pengajuan sewa disetujui');

            return $lease;
        });
    }

    /**
     * FR-BOOK-05: tolak dengan alasan.
     */
    public function reject(BookingRequest $booking, User $actor, string $reason): BookingRequest
    {
        return DB::transaction(function () use ($booking, $actor, $reason) {
            $booking = BookingRequest::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! $booking->isPending()) {
                throw new BusinessRuleException('Pengajuan ini sudah diproses.');
            }

            $booking->forceFill([
                'status' => BookingStatus::Rejected,
                'reject_reason' => $reason,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ])->save();

            activity('booking')->performedOn($booking)->causedBy($actor)->event('rejected')
                ->withProperties(['reason' => $reason])
                ->log('Pengajuan sewa ditolak');

            return $booking;
        });
    }

    /**
     * FR-BOOK-07: pending lebih dari N hari menjadi expired.
     */
    public function expireStale(): int
    {
        return BookingRequest::query()
            ->where('status', BookingStatus::Pending)
            ->where('created_at', '<', now()->subDays((int) config('kost.booking_expire_days')))
            ->update(['status' => BookingStatus::Expired, 'updated_at' => now()]);
    }
}
