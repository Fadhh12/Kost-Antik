<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $tenant;

    private Lease $lease;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->manager = User::factory()->manager()->create();
        $this->tenant = User::factory()->tenant()->create();
        $room = Room::factory()->for(Property::factory()->state(['manager_id' => $this->manager->id]))->create(['monthly_price' => 1_000_000]);
        $this->lease = app(LeaseService::class)->createManual($this->tenant, $room, today(), 2, User::factory()->owner()->create());
        $this->invoice = $this->lease->invoices()->orderBy('sequence')->first();
    }

    private function upload(?User $as = null, array $overrides = [])
    {
        return $this->actingAs($as ?? $this->tenant)->post("/app/invoices/{$this->invoice->id}/payments", array_merge([
            'invoice_id' => $this->invoice->id,
            'amount' => 1_000_000,
            'method' => 'transfer',
            'paid_at' => today()->toDateString(),
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ], $overrides));
    }

    public function test_tenant_uploads_proof(): void
    {
        $this->upload()->assertRedirect(route('app.invoices.index', ['tab' => 'menunggu']));

        $payment = Payment::first();
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->assertSame(InvoiceStatus::PendingVerification, $this->invoice->fresh()->status);
    }

    public function test_amount_must_equal_invoice_and_proof_type_is_checked(): void
    {
        $this->upload(overrides: ['amount' => 900_000])->assertSessionHasErrors('amount');
        $this->upload(overrides: ['proof' => UploadedFile::fake()->create('virus.exe', 10)])->assertSessionHasErrors('proof');
        $this->upload(overrides: ['paid_at' => today()->addDay()->toDateString()])->assertSessionHasErrors('paid_at');
        $this->assertSame(0, Payment::count());
    }

    public function test_tenant_cannot_pay_someone_elses_invoice(): void
    {
        $this->upload(User::factory()->tenant()->create())->assertForbidden();
    }

    public function test_proof_is_private(): void
    {
        $this->upload();
        $payment = Payment::first();
        $url = "/payments/{$payment->id}/proof";

        $this->actingAs($this->tenant)->get($url)->assertOk();
        $this->actingAs($this->manager)->get($url)->assertOk();
        $this->actingAs(User::factory()->owner()->create())->get($url)->assertOk();
        $this->actingAs(User::factory()->tenant()->create())->get($url)->assertForbidden();
        $this->actingAs(User::factory()->manager()->create())->get($url)->assertForbidden();
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
    }

    public function test_manager_verifies_payment(): void
    {
        $this->upload();
        $payment = Payment::first();

        $this->actingAs($this->manager)->get('/admin/payments')->assertOk()->assertSee($this->invoice->number);
        $this->actingAs($this->manager)->patch("/admin/payments/{$payment->id}/verify")->assertSessionHas('success');

        $this->assertSame(PaymentStatus::Verified, $payment->fresh()->status);
        $this->assertSame(InvoiceStatus::Paid, $this->invoice->fresh()->status);
        $this->assertSame(1_000_000, $this->lease->fresh()->paid_amount);
        $this->assertDatabaseHas('activity_log', ['subject_id' => $payment->id, 'event' => 'verified']);
    }

    public function test_manager_rejects_with_reason(): void
    {
        $this->upload();
        $payment = Payment::first();

        $this->actingAs($this->manager)->patch("/admin/payments/{$payment->id}/reject", ['reason' => 'Bukti buram, nominal tidak terbaca.'])->assertSessionHas('success');

        $this->assertSame(PaymentStatus::Rejected, $payment->fresh()->status);
        $this->assertSame(InvoiceStatus::Unpaid, $this->invoice->fresh()->status);
    }

    public function test_other_manager_cannot_verify(): void
    {
        $this->upload();

        $this->actingAs(User::factory()->manager()->create())->patch('/admin/payments/'.Payment::first()->id.'/verify')->assertForbidden();
    }

    public function test_cash_recording_and_owner_revocation(): void
    {
        $this->actingAs($this->manager)->post("/admin/invoices/{$this->invoice->id}/payments/cash", ['paid_at' => today()->toDateString()])->assertSessionHas('success');
        $payment = Payment::first();
        $this->assertSame(InvoiceStatus::Paid, $this->invoice->fresh()->status);

        // Hanya owner yang boleh membatalkan verifikasi.
        $this->actingAs($this->manager)->patch("/admin/payments/{$payment->id}/revoke", ['reason' => 'Salah input tagihan.'])->assertForbidden();
        $this->actingAs(User::factory()->owner()->create())->patch("/admin/payments/{$payment->id}/revoke", ['reason' => 'Salah input tagihan.'])->assertSessionHas('success');

        $this->assertSame(InvoiceStatus::Unpaid, $this->invoice->fresh()->status);
        $this->assertSame(0, $this->lease->fresh()->paid_amount);
        $this->assertDatabaseHas('payments', ['id' => $payment->id]); // BR-07: tidak dihapus
    }
}
