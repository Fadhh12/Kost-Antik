<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Semua tagihan dengan filter status/periode/gedung.
 */
class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = InvoiceStatus::tryFrom((string) $request->query('status'));
        $period = $request->filled('periode') && preg_match('/^\d{4}-\d{2}$/', $request->periode)
            ? Carbon::createFromFormat('Y-m', $request->periode)->startOfMonth()
            : null;

        $base = Invoice::forManager($user)
            ->when($request->filled('property'), fn ($q) => $q->whereHas('lease.room', fn ($r) => $r->where('property_id', $request->integer('property'))))
            ->when($period, fn ($q) => $q->whereBetween('period_start', [$period, $period->copy()->endOfMonth()]));

        $summary = (clone $base)
            ->selectRaw('status, count(*) as total, sum(amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn ($row) => $row->status->value);

        $invoices = (clone $base)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('number', 'like', '%'.$request->q.'%')
                ->orWhereHas('lease.user', fn ($u) => $u->where('name', 'like', '%'.$request->q.'%'))))
            ->with(['lease.user', 'lease.room.property', 'pendingPayment'])
            ->orderByRaw("case status when 'overdue' then 0 when 'pending_verification' then 1 when 'unpaid' then 2 else 3 end")
            ->orderBy('due_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'summary' => $summary,
            'status' => $status,
            'properties' => Property::forManager($user)->orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
