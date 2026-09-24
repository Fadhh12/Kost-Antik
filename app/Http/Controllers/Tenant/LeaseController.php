<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-12: riwayat kontrak & detail (T4).
 */
class LeaseController extends Controller
{
    public function index(Request $request): View
    {
        return view('tenant.leases.index', [
            'leases' => $request->user()->leases()
                ->with(['room.property.coverImage', 'review'])
                ->orderByRaw('case when status = ? then 0 else 1 end', [LeaseStatus::Active->value])
                ->latest('start_date')
                ->get(),
        ]);
    }

    public function show(Lease $lease): View
    {
        $this->authorize('view', $lease);

        $lease->load(['room.property', 'invoices.payments', 'review']);

        return view('tenant.leases.show', [
            'lease' => $lease,
            'canReview' => $this->canReview($lease),
        ]);
    }

    /**
     * FR-REV-01: aktif minimal N hari atau sudah selesai, satu ulasan per kontrak.
     */
    private function canReview(Lease $lease): bool
    {
        if ($lease->review) {
            return false;
        }

        return $lease->status === LeaseStatus::Completed
            || ($lease->isActive() && $lease->start_date->lte(today()->subDays((int) config('kost.review_min_active_days'))));
    }
}
