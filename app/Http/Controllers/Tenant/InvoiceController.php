<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-11: tagihan penyewa (T2). Tab: belum dibayar / menunggu verifikasi / lunas.
 */
class InvoiceController extends Controller
{
    public const TABS = [
        'belum' => [InvoiceStatus::Unpaid, InvoiceStatus::Overdue],
        'menunggu' => [InvoiceStatus::PendingVerification],
        'lunas' => [InvoiceStatus::Paid],
    ];

    public function index(Request $request, MidtransService $midtrans): View
    {
        $user = $request->user();
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'belum';

        $base = Invoice::ownedBy($user);

        $counts = collect(self::TABS)->map(fn ($statuses) => (clone $base)->whereIn('status', $statuses)->count());

        $invoices = (clone $base)
            ->whereIn('status', self::TABS[$tab])
            ->with(['lease.room.property', 'payments' => fn ($q) => $q->latest()])
            ->when($tab === 'lunas', fn ($q) => $q->orderByDesc('period_start'), fn ($q) => $q->orderBy('due_date'))
            ->paginate(10)
            ->withQueryString();

        // Buka modal bayar langsung dari dashboard (?bayar=id).
        $payInvoice = $request->filled('bayar')
            ? (clone $base)->with('lease.room.property')->find($request->integer('bayar'))
            : null;

        return view('tenant.invoices.index', [
            ...compact('invoices', 'tab', 'counts', 'payInvoice'),
            'midtransActive' => $midtrans->isActive(),
            'midtransClientKey' => $midtrans->clientKey(),
            'midtransProduction' => $midtrans->isProduction(),
        ]);
    }
}
