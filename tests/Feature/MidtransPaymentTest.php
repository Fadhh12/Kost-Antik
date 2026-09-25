<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-03-01');

        $this->tenant = User::factory()->tenant()->create();
        $this->lease = app(LeaseService::class)->createManual(
            $this->tenant,
            Room::factory()->create(['monthly_price' => 1_000_000]),
            Carbon::parse('2026-03-01'),
            1,
            User::factory()->owner()->create(),
        );
    }

    private function invoice(): Invoice
    {
        return $this->lease->invoices()->first();
    }

    private function activateMidtrans(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test',
            'services.midtrans.client_key' => 'SB-Mid-client-test',
            'services.midtrans.is_production' => false,
        ]);
    }

    private function validWebhookPayload(Invoice $invoice, string $orderId): array
    {
        $statusCode = '200';
        $grossAmount = number_format($invoice->amount, 2, '.', '');

        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', $orderId.$statusCode.$grossAmount.'SB-Mid-server-test'),
        ];
    }

    public function test_pay_online_is_unavailable_when_midtrans_not_configured(): void
    {
        $this->actingAs($this->tenant)
            ->postJson("/app/invoices/{$this->invoice()->id}/pay-online")
            ->assertStatus(422);
    }

    public function test_tenant_gets_a_snap_token_when_midtrans_is_configured(): void
    {
        $this->activateMidtrans();
        Http::fake(['app.sandbox.midtrans.com/*' => Http::response(['token' => 'snap-token-abc'], 200)]);

        $this->actingAs($this->tenant)
            ->postJson("/app/invoices/{$this->invoice()->id}/pay-online")
            ->assertOk()
            ->assertJson(['token' => 'snap-token-abc', 'client_key' => 'SB-Mid-client-test']);

        Http::assertSent(fn ($request) => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && str_starts_with($request['transaction_details']['order_id'], $this->invoice()->number.'-')
            && $request['transaction_details']['gross_amount'] === $this->invoice()->amount);
    }

    public function test_other_tenants_cannot_request_a_snap_token_for_someone_elses_invoice(): void
    {
        $this->activateMidtrans();

        $this->actingAs(User::factory()->tenant()->create())
            ->postJson("/app/invoices/{$this->invoice()->id}/pay-online")
            ->assertForbidden();
    }

    public function test_webhook_marks_invoice_paid_on_valid_settlement_notification(): void
    {
        $this->activateMidtrans();
        $invoice = $this->invoice();
        $orderId = $invoice->number.'-1735000000';

        $this->postJson('/webhooks/midtrans', $this->validWebhookPayload($invoice, $orderId))->assertOk();

        $invoice->refresh();
        $this->assertTrue($invoice->status === InvoiceStatus::Paid);

        $payment = Payment::where('gateway_reference', $orderId)->first();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->method === PaymentMethod::Midtrans);
        $this->assertNull($payment->verified_by);
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $this->activateMidtrans();
        $invoice = $this->invoice();
        $payload = $this->validWebhookPayload($invoice, $invoice->number.'-1735000000');
        $payload['signature_key'] = 'tampered';

        $this->postJson('/webhooks/midtrans', $payload)->assertStatus(403);

        $this->assertTrue($invoice->fresh()->status !== InvoiceStatus::Paid);
    }

    public function test_webhook_is_idempotent_on_duplicate_notifications(): void
    {
        $this->activateMidtrans();
        $invoice = $this->invoice();
        $orderId = $invoice->number.'-1735000000';
        $payload = $this->validWebhookPayload($invoice, $orderId);

        $this->postJson('/webhooks/midtrans', $payload)->assertOk();
        $this->postJson('/webhooks/midtrans', $payload)->assertOk();

        $this->assertSame(1, Payment::where('gateway_reference', $orderId)->count());
        $this->assertSame($invoice->amount, $this->lease->fresh()->paid_amount);
    }
}
