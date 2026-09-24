<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePaymentRequest;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Invoice $invoice, PaymentService $payments): RedirectResponse
    {
        $payments->submit(
            $invoice,
            $request->user(),
            $request->integer('amount'),
            Carbon::parse($request->date('paid_at')),
            $request->file('proof'),
        );

        return redirect()->route('app.invoices.index', ['tab' => 'menunggu'])
            ->with('success', 'Bukti bayar terkirim. Pengelola akan memverifikasi dalam 1x24 jam.');
    }
}
