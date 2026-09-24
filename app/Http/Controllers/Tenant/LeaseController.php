<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Services\ReviewService;
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

    public function show(Lease $lease, ReviewService $reviews): View
    {
        $this->authorize('view', $lease);

        $lease->load(['room.property', 'invoices.payments', 'review']);

        return view('tenant.leases.show', [
            'lease' => $lease,
            'canReview' => ! $lease->review && $reviews->eligible($lease),
        ]);
    }
}
