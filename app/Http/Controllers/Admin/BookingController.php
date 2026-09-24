<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReasonRequest;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-17: antrean booking (owner semua gedung, pengelola gedungnya).
 */
class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->query('status', 'pending');
        if ($status !== 'all' && ! BookingStatus::tryFrom($status)) {
            $status = 'pending';
        }
        $base = BookingRequest::forManager($user)
            ->when($request->filled('property'), fn ($q) => $q->whereHas('room', fn ($r) => $r->where('property_id', $request->integer('property'))));

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $bookings = (clone $base)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with(['user.instance', 'room.property', 'reviewer', 'lease'])
            ->orderByRaw('case when status = ? then 0 else 1 end', [BookingStatus::Pending->value])
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'counts' => $counts,
            'properties' => Property::forManager($user)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function approve(Request $request, BookingRequest $booking): RedirectResponse
    {
        $this->authorize('process', $booking);
        $lease = $this->bookings->approve($booking, $request->user());

        return back()->with('success', 'Pengajuan disetujui. Kontrak '.$lease->code.' dan '.$lease->duration_months.' tagihan sudah dibuat.');
    }

    public function reject(ReasonRequest $request, BookingRequest $booking): RedirectResponse
    {
        $this->authorize('process', $booking);
        $this->bookings->reject($booking, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Pengajuan ditolak dan alasannya dikirim ke penyewa.');
    }
}
