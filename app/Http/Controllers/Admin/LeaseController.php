<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeaseRequest;
use App\Http\Requests\Admin\TerminateLeaseRequest;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

/**
 * F-19 & FR-LEASE: daftar kontrak, kontrak walk-in, detail, dan terminasi.
 */
class LeaseController extends Controller
{
    public function __construct(private LeaseService $leases) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = LeaseStatus::tryFrom((string) $request->query('status'));

        $leases = Lease::forManager($user)
            ->with(['user', 'room.property'])
            ->withCount(['invoices as overdue_invoices_count' => fn ($q) => $q->where('status', InvoiceStatus::Overdue)])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->boolean('tunggakan'), fn ($q) => $q->withArrears())
            ->when($request->filled('property'), fn ($q) => $q->whereHas('room', fn ($r) => $r->where('property_id', $request->integer('property'))))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('code', 'like', '%'.$request->q.'%')
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->q.'%'))))
            ->orderByRaw('case when status = ? then 0 else 1 end', [LeaseStatus::Active->value])
            ->latest('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.leases.index', [
            'leases' => $leases,
            'properties' => Property::forManager($user)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Lease::class);
        $user = $request->user();

        $tenants = User::tenants()
            ->where('status', UserStatus::Accepted)
            ->whereDoesntHave('leases', fn ($q) => $q->where('status', LeaseStatus::Active))
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'gender']);

        $properties = Property::forManager($user)
            ->where('status', PropertyStatus::Active)
            ->with(['rooms' => fn ($q) => $q->where('status', RoomStatus::Available)])
            ->orderBy('name')
            ->get();

        return view('admin.leases.create', compact('tenants', 'properties'));
    }

    public function store(StoreLeaseRequest $request, PaymentService $payments): RedirectResponse
    {
        $tenant = User::findOrFail($request->integer('user_id'));
        $room = Room::with('property')->findOrFail($request->integer('room_id'));
        // BR-08: pengelola hanya untuk kamar di gedungnya.
        $this->authorize('update', $room);

        $lease = DB::transaction(function () use ($request, $tenant, $room, $payments) {
            $lease = $this->leases->createManual($tenant, $room, Carbon::parse($request->date('start_date')), $request->integer('duration_months'), $request->user());

            // Opsional: langsung catat pembayaran bulan pertama secara tunai.
            if ($request->boolean('pay_first_cash')) {
                $payments->recordCash($lease->invoices()->orderBy('sequence')->first(), $request->user(), today());
            }

            return $lease;
        });

        return redirect()->route('admin.leases.show', $lease)->with('success', 'Kontrak '.$lease->code.' dibuat dengan '.$lease->duration_months.' tagihan.');
    }

    public function show(Lease $lease): View
    {
        $this->authorize('view', $lease);

        $lease->load(['user.instance', 'room.property', 'booking', 'creator', 'review', 'invoices.payments.verifier']);

        return view('admin.leases.show', [
            'lease' => $lease,
            'history' => Activity::where('subject_type', Lease::class)->where('subject_id', $lease->id)->with('causer')->latest()->get(),
        ]);
    }

    public function terminate(TerminateLeaseRequest $request, Lease $lease): RedirectResponse
    {
        $this->leases->terminate($lease, Carbon::parse($request->date('terminated_at')), $request->validated('reason'), $request->user());

        return back()->with('success', 'Kontrak '.$lease->code.' diakhiri. Kamar kembali tersedia.');
    }
}
