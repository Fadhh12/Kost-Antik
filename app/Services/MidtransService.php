<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integrasi Midtrans Snap (REST langsung, tanpa SDK pihak ketiga).
 * https://docs.midtrans.com/reference/snap-1
 */
class MidtransService
{
    public function isActive(): bool
    {
        return filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key'));
    }

    public function clientKey(): ?string
    {
        return config('services.midtrans.client_key');
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    /**
     * Buat transaksi Snap untuk satu tagihan, kembalikan snap token.
     * order_id diberi akhiran waktu agar setiap percobaan bayar unik (retry-safe).
     */
    public function createSnapToken(Invoice $invoice): string
    {
        if (! $this->isActive()) {
            throw new RuntimeException('Midtrans belum dikonfigurasi.');
        }

        $tenant = $invoice->lease->user;
        $orderId = $invoice->number.'-'.now()->timestamp;

        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->post($this->snapUrl(), [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $invoice->amount,
                ],
                'customer_details' => [
                    'first_name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                ],
                'item_details' => [[
                    'id' => $invoice->number,
                    'price' => $invoice->amount,
                    'quantity' => 1,
                    'name' => 'Tagihan '.$invoice->periodLabel(),
                ]],
            ])
            ->throw();

        return $response->json('token');
    }

    /**
     * Cocokkan signature_key notifikasi webhook (dok. Midtrans: sha512).
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.config('services.midtrans.server_key'));

        return hash_equals($expected, $signatureKey);
    }

    private function snapUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }
}
