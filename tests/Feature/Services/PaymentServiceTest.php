<?php

namespace Tests\Feature\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Carbon::setTestNow('2026-03-01');

        $this->owner = User::factory()->owner()->create();
        $this->tenant = User::factory()->tenant()->create();
        $this->lease = app(LeaseService::class)->createManual(
            $this->tenant,
            Room::factory()->create(['monthly_price' => 1_000_000]),
            Carbon::parse('2026-03-01'),
            3,
            $this->owner,
        );
    }

    private function submit(?int $amount = null)
    {
        $invoice = $this->lease->invoices()->first();

        return app(PaymentService::class)->submit(
            $invoice,
            $this->tenant,
            $amount ?? $invoice->amount,
            today(),
            UploadedFile::fake()->image('bukti.jpg'),
        );
    }

    public function test_submit_stores_private_proof_and_marks_invoice_pending(): void
    {
        $payment = $this->submit();

        Storage::disk('local')->assertExists($payment->proof_path);
        $this->assertSame(InvoiceStatus::PendingVerification, $payment->invoice->fresh()->status);
    }

    public function test_amount_must_match_invoice(): void
    {
        $this->expectException(BusinessRuleException::class);
        $this->submit(500_000);
    }

    public function test_only_one_pending_payment_per_invoice(): void
    {
        $this->submit();
        $this->expectException(BusinessRuleException::class);
        $this->submit();
    }

    public function test_verify_marks_invoice_paid_and_increments_lease(): void
    {
        $payment = app(PaymentService::class)->verify($this->submit(), $this->owner);

        $this->assertSame(PaymentStatus::Verified, $payment->status);
        $this->assertSame(InvoiceStatus::Paid, $payment->invoice->fresh()->status);
        $this->assertSame(1_000_000, $this->lease->fresh()->paid_amount);
    }

    public function test_reject_returns_invoice_to_overdue_when_past_due(): void
    {
        $payment = $this->submit();
        Carbon::setTestNow('2026-03-10');

        app(PaymentService::class)->reject($payment, $this->owner, 'Nominal di bukti tidak terbaca.');

        $this->assertSame(InvoiceStatus::Overdue, $payment->invoice->fresh()->status);
        $this->assertSame(0, $this->lease->fresh()->paid_amount);
    }

    public function test_cash_payment_is_verified_immediately(): void
    {
        $payment = app(PaymentService::class)->recordCash($this->lease->invoices()->first(), $this->owner, today());

        $this->assertSame(PaymentStatus::Verified, $payment->status);
        $this->assertSame(1_000_000, $this->lease->fresh()->paid_amount);
    }

    public function test_revoking_verification_restores_balance(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->recordCash($this->lease->invoices()->first(), $this->owner, today());

        $service->revokeVerification($payment, $this->owner, 'Salah pilih tagihan.');

        $this->assertSame(0, $this->lease->fresh()->paid_amount);
        $this->assertSame(InvoiceStatus::Unpaid, $payment->invoice->fresh()->status);
    }
}
