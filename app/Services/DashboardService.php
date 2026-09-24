<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\RoomStatus;
use App\Enums\UserStatus;
use App\Models\BookingRequest;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;

class DashboardService
{
    /**
     * FR-DASH-01/02: metrik owner (semua gedung) atau pengelola (gedungnya).
     *
     * @return array<string, mixed>
     */
    public function forStaff(User $user): array
    {
        $properties = Property::forManager($user)
            ->withCatalogStats()
            ->withCount(['rooms as maintenance_rooms_count' => fn ($q) => $q->where('status', RoomStatus::Maintenance)])
            ->orderBy('name')
            ->get();

        $totalRooms = (int) $properties->sum('rooms_count');
        $occupied = (int) $properties->sum('occupied_rooms_count');
        $bookable = $totalRooms - (int) $properties->sum('maintenance_rooms_count');

        $monthStart = now()->startOfMonth();
        $revenueThisMonth = (int) Payment::forManager($user)
            ->where('status', PaymentStatus::Verified)
            ->whereBetween('verified_at', [$monthStart, now()])
            ->sum('amount');
        $revenueLastMonth = (int) Payment::forManager($user)
            ->where('status', PaymentStatus::Verified)
            ->whereBetween('verified_at', [$monthStart->copy()->subMonth(), $monthStart->copy()->subSecond()])
            ->sum('amount');

        $overdue = Invoice::forManager($user)->where('status', InvoiceStatus::Overdue);

        return [
            'properties' => $properties,
            'totalRooms' => $totalRooms,
            'occupied' => $occupied,
            'occupancy' => $bookable > 0 ? round($occupied / $bookable * 100, 1) : 0.0,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueLastMonth' => $revenueLastMonth,
            'overdueCount' => (clone $overdue)->count(),
            'overdueAmount' => (int) (clone $overdue)->sum('amount'),
            'pendingBookings' => BookingRequest::forManager($user)->where('status', BookingStatus::Pending)
                ->with(['user', 'room.property'])->oldest()->limit(5)->get(),
            'pendingPayments' => Payment::forManager($user)->where('status', PaymentStatus::Pending)
                ->with(['invoice.lease.user', 'invoice.lease.room.property'])->oldest()->limit(5)->get(),
            'pendingUsers' => $user->isOwner()
                ? User::role(Role::Tenant->value)->where('status', UserStatus::Pending)->oldest()->limit(5)->get()
                : collect(),
            'expiringLeases' => Lease::forManager($user)->where('status', LeaseStatus::Active)
                ->whereBetween('end_date', [today(), today()->addDays(30)])
                ->with(['user', 'room.property'])->orderBy('end_date')->limit(5)->get(),
        ];
    }

    /**
     * Pendapatan terverifikasi per bulan (6 bulan terakhir, termasuk bulan berjalan).
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function revenueSeries(User $user, int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $totals = Payment::forManager($user)
            ->where('status', PaymentStatus::Verified)
            ->where('verified_at', '>=', $start)
            ->get(['amount', 'verified_at'])
            ->groupBy(fn (Payment $p) => $p->verified_at->format('Y-m'))
            ->map->sum('amount');

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->locale('id')->translatedFormat('M Y');
            $values[] = (int) ($totals[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * FR-DASH-03: ringkasan untuk penyewa.
     *
     * @return array<string, mixed>
     */
    public function forTenant(User $user): array
    {
        $activeLease = $user->leases()
            ->where('status', LeaseStatus::Active)
            ->with(['room.property.coverImage'])
            ->latest('start_date')
            ->first();

        $nextInvoice = $activeLease?->invoices()
            ->whereIn('status', InvoiceStatus::outstanding())
            ->orderBy('sequence')
            ->first();

        $lastLease = $activeLease ? null : $user->leases()
            ->whereIn('status', [LeaseStatus::Completed, LeaseStatus::Terminated])
            ->with(['room.property', 'review'])
            ->latest('end_date')
            ->first();

        return [
            'activeLease' => $activeLease,
            'nextInvoice' => $nextInvoice,
            'overdueCount' => $activeLease?->invoices()->where('status', InvoiceStatus::Overdue)->count() ?? 0,
            'latestBooking' => $user->bookings()->with('room.property')->latest()->first(),
            'lastLease' => $lastLease,
        ];
    }
}
