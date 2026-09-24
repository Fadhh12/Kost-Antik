<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\FacilityType;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\Property;
use App\Models\User;

/**
 * F-03/F-04: data halaman detail kost untuk publik.
 */
class PropertyDetailService
{
    /**
     * @return array<string, mixed>
     */
    public function forPublic(Property $property, ?User $viewer): array
    {
        $property->load([
            'images',
            'facilities' => fn ($q) => $q->where('type', FacilityType::Shared)->orderBy('name'),
            'rooms.facilities',
        ])->loadCount(['publishedReviews as reviews_count'])->loadAvg('publishedReviews as rating_avg', 'rating');

        $rooms = $property->rooms;
        $available = $rooms->where('status', RoomStatus::Available);

        return [
            'property' => $property,
            'rooms' => $rooms,
            'availableCount' => $available->count(),
            'startingPrice' => (int) ($available->min('monthly_price') ?? $rooms->min('monthly_price') ?? 0),
            'reviews' => $property->publishedReviews()->with('user')->latest()->limit(6)->get(),
            'ratingBreakdown' => $property->publishedReviews()->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating'),
            'blocker' => $this->bookingBlocker($property, $viewer),
        ];
    }

    /**
     * Alasan umum penyewa tidak bisa mengajukan sewa di gedung ini (null = boleh).
     * Ketersediaan per kamar dicek terpisah.
     */
    public function bookingBlocker(Property $property, ?User $viewer): ?string
    {
        if (! $viewer) {
            return null; // Tamu diarahkan ke login/daftar (F-04).
        }

        return match (true) {
            ! $viewer->isTenant() => 'Akun pengelola tidak bisa mengajukan sewa.',
            $viewer->isPending() => 'Akunmu masih menunggu verifikasi pemilik. Pengajuan bisa dikirim setelah akun diverifikasi.',
            ! $viewer->isAccepted() => 'Akunmu tidak aktif.',
            $viewer->leases()->where('status', LeaseStatus::Active)->exists() => 'Kamu masih punya kontrak aktif. Satu penyewa hanya boleh punya satu kontrak aktif.',
            $viewer->bookings()->where('status', BookingStatus::Pending)->exists() => 'Kamu masih punya pengajuan sewa yang sedang diproses.',
            ! $property->gender_target->accepts($viewer->gender) => 'Kost ini khusus '.strtolower($property->gender_target->label()).'.',
            default => null,
        };
    }
}
