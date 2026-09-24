<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReasonRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * F-18: verifikasi pembayaran (A7) dan catat tunai.
 */
class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = PaymentStatus::tryFrom((string) $request->query('status')) ?? PaymentStatus::Pending;

        $base = Payment::forManager($user)
            ->when($request->filled('property'), fn ($q) => $q->whereHas('invoice.lease.room', fn ($r) => $r->where('property_id', $request->integer('property'))));

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $payments = (clone $base)
            ->where('status', $status)
            ->with(['invoice.lease.user', 'invoice.lease.room.property', 'submitter', 'verifier'])
            ->when($status === PaymentStatus::Pending, fn ($q) => $q->oldest(), fn ($q) => $q->latest('verified_at'))
            ->paginate(10)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'status' => $status,
            'counts' => $counts,
            'properties' => Property::forManager($user)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);
        $this->payments->verify($payment, $request->user());

        return back()->with('success', 'Pembayaran '.$payment->invoice->number.' terverifikasi. Tagihan lunas.');
    }

    public function reject(ReasonRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);
        $this->payments->reject($payment, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Pembayaran ditolak. Penyewa diminta mengunggah ulang bukti.');
    }

    /**
     * FR-PAY-05 (P1): owner membatalkan verifikasi yang keliru.
     */
    public function revoke(ReasonRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('revoke', $payment);
        $this->payments->revokeVerification($payment, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Verifikasi dibatalkan dan tercatat di log aktivitas.');
    }

    /**
     * FR-PAY-04: catat pembayaran tunai (langsung terverifikasi).
     */
    public function cash(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('recordCash', $invoice);

        $data = $request->validate([
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:3072'],
        ]);

        $this->payments->recordCash($invoice, $request->user(), Carbon::parse($data['paid_at']), $request->file('proof'));

        return back()->with('success', 'Pembayaran tunai '.$invoice->number.' dicatat. Tagihan lunas.');
    }
}
