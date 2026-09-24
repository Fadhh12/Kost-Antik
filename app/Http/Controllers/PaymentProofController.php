<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * FR-PAY-06: bukti bayar di disk privat, hanya lewat route ini setelah dicek Policy.
 */
class PaymentProofController extends Controller
{
    public function __invoke(Payment $payment): StreamedResponse
    {
        $this->authorize('viewProof', $payment);

        abort_unless(app(PaymentService::class)->proofExists($payment), 404);

        return Storage::disk(PaymentService::PROOF_DISK)->response(
            $payment->proof_path,
            'bukti-'.$payment->invoice->number.'.'.pathinfo($payment->proof_path, PATHINFO_EXTENSION),
            [
                'Cache-Control' => 'private, max-age=600',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }
}
