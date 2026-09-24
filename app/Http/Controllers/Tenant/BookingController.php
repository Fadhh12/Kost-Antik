<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreBookingRequest;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request): View
    {
        return view('tenant.bookings.index', [
            'bookings' => $request->user()->bookings()
                ->with(['room.property.coverImage', 'lease'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $room = Room::with('property')->findOrFail($request->integer('room_id'));

        $this->bookings->create(
            $request->user(),
            $room,
            Carbon::parse($request->date('start_date')),
            $request->integer('duration_months'),
            $request->input('note'),
        );

        return redirect()->route('app.bookings.index')
            ->with('success', 'Pengajuan sewa terkirim. Pengelola akan memprosesnya paling lama '.config('kost.booking_expire_days').' hari.');
    }

    public function cancel(Request $request, BookingRequest $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $this->bookings->cancel($booking, $request->user());

        return back()->with('success', 'Pengajuan sewa dibatalkan.');
    }
}
