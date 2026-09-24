<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Models\User;

class DashboardService
{
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
