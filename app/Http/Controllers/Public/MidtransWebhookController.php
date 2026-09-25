<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi server-to-server Midtrans. https://docs.midtrans.com/docs/https-notification-webhooks
 * Tidak lewat auth/CSRF (lihat bootstrap/app.php); keamanan lewat signature_key.
 */
class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans, PaymentService $payments): JsonResponse
    {
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key');

        if (! $midtrans->isActive() || ! $midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status', 'accept');
        $settled = in_array($transactionStatus, ['settlement', 'capture'], true) && $fraudStatus === 'accept';

        if ($settled && $invoiceNumber = $this->invoiceNumberFromOrderId($orderId)) {
            $invoice = Invoice::where('number', $invoiceNumber)->first();

            if ($invoice?->isPayable()) {
                $payments->recordFromGateway($invoice, (int) round((float) $grossAmount), $orderId);
            }
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * order_id = "{nomor_invoice}-{unix_timestamp}", lihat MidtransService::createSnapToken().
     */
    private function invoiceNumberFromOrderId(string $orderId): ?string
    {
        $pos = strrpos($orderId, '-');

        return $pos === false ? null : substr($orderId, 0, $pos);
    }
}
