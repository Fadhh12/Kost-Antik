<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\LeaseStatus;
use App\Enums\PropertyStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Room;
use App\Models\User;

/**
 * Syarat menyewa yang sama untuk booking & kontrak walk-in
 * (FR-BOOK-01..03, FR-LEASE-01, BR-01, BR-02).
 */
class RentalEligibility
{
    /**
     * @param  int|null  $ignoreBookingId  booking yang sedang diproses (tidak dihitung sebagai booking pending lain)
     * @param  bool  $checkPendingBooking  false untuk kontrak manual
     *
     * @throws BusinessRuleException
     */
    public function assert(User $tenant, Room $room, ?int $ignoreBookingId = null, bool $checkPendingBooking = true): void
    {
        if ($message = $this->violation($tenant, $room, $ignoreBookingId, $checkPendingBooking)) {
            throw new BusinessRuleException($message);
        }
    }

    public function violation(User $tenant, Room $room, ?int $ignoreBookingId = null, bool $checkPendingBooking = true): ?string
    {
        $room->loadMissing('property');

        if (! $tenant->isTenant()) {
            return 'Hanya akun penyewa yang bisa menyewa kamar.';
        }

        if (! $tenant->isAccepted()) {
            return 'Akun penyewa belum diverifikasi pemilik.';
        }

        if ($tenant->leases()->where('status', LeaseStatus::Active)->exists()) {
            return 'Penyewa masih memiliki kontrak aktif.';
        }

        if ($checkPendingBooking && $tenant->bookings()
            ->where('status', BookingStatus::Pending)
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->exists()) {
            return 'Penyewa masih punya pengajuan sewa yang menunggu diproses.';
        }

        if ($room->property->status !== PropertyStatus::Active) {
            return 'Gedung ini sedang tidak menerima penyewa.';
        }

        if (! $room->property->gender_target->accepts($tenant->gender)) {
            return 'Kost ini khusus '.strtolower($room->property->gender_target->label()).'.';
        }

        if (! $room->isAvailable()) {
            return 'Kamar '.$room->code.' sudah tidak tersedia.';
        }

        return null;
    }
}
