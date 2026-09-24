<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    /** Disk privat untuk bukti bayar (FR-PAY-06). */
    public const PROOF_DISK = 'local';

    /**
     * FR-PAY-01/02: penyewa mengunggah bukti transfer.
     */
    public function submit(Invoice $invoice, User $tenant, int $amount, CarbonInterface $paidAt, UploadedFile $proof): Payment
    {
        return DB::transaction(function () use ($invoice, $tenant, $amount, $paidAt, $proof) {
            $invoice = $this->lockInvoice($invoice);

            if ($invoice->lease->user_id !== $tenant->id) {
                throw new BusinessRuleException('Tagihan ini bukan milikmu.');
            }

            $this->assertPayable($invoice, $amount);

            $path = $proof->store('payment-proofs/'.now()->format('Y/m'), self::PROOF_DISK);

            $payment = $invoice->payments()->create([
                'amount' => $amount,
                'method' => PaymentMethod::Transfer,
                'paid_at' => $paidAt,
                'proof_path' => $path,
                'status' => PaymentStatus::Pending,
                'submitted_by' => $tenant->id,
            ]);

            $invoice->update(['status' => InvoiceStatus::PendingVerification]);

            activity('payment')->performedOn($payment)->causedBy($tenant)->event('submitted')
                ->withProperties(['invoice' => $invoice->number, 'amount' => $amount])
                ->log('Bukti bayar diunggah');

            return $payment;
        });
    }

    /**
     * FR-PAY-04: pengelola mencatat pembayaran tunai (langsung terverifikasi).
     */
    public function recordCash(Invoice $invoice, User $actor, CarbonInterface $paidAt, ?UploadedFile $proof = null): Payment
    {
        return DB::transaction(function () use ($invoice, $actor, $paidAt, $proof) {
            $invoice = $this->lockInvoice($invoice);
            $this->assertPayable($invoice, $invoice->amount);

            $payment = $invoice->payments()->create([
                'amount' => $invoice->amount,
                'method' => PaymentMethod::Cash,
                'paid_at' => $paidAt,
                'proof_path' => $proof?->store('payment-proofs/'.now()->format('Y/m'), self::PROOF_DISK),
                'status' => PaymentStatus::Pending,
                'submitted_by' => $actor->id,
            ]);

            $this->markVerified($payment, $invoice, $actor);

            activity('payment')->performedOn($payment)->causedBy($actor)->event('cash_recorded')
                ->withProperties(['invoice' => $invoice->number, 'amount' => $payment->amount])
                ->log('Pembayaran tunai dicatat');

            return $payment;
        });
    }

    /**
     * FR-PAY-03: verifikasi -> invoice lunas, paid_amount kontrak bertambah.
     */
    public function verify(Payment $payment, User $actor): Payment
    {
        return DB::transaction(function () use ($payment, $actor) {
            $invoice = $this->lockInvoice($payment->invoice);
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Pending) {
                throw new BusinessRuleException('Pembayaran ini sudah diproses.');
            }

            $this->markVerified($payment, $invoice, $actor);

            activity('payment')->performedOn($payment)->causedBy($actor)->event('verified')
                ->withProperties(['invoice' => $invoice->number, 'amount' => $payment->amount])
                ->log('Pembayaran diverifikasi');

            return $payment;
        });
    }

    /**
     * FR-PAY-03: tolak -> invoice kembali belum dibayar / terlambat.
     */
    public function reject(Payment $payment, User $actor, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $actor, $reason) {
            $invoice = $this->lockInvoice($payment->invoice);
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Pending) {
                throw new BusinessRuleException('Pembayaran ini sudah diproses.');
            }

            $payment->update([
                'status' => PaymentStatus::Rejected,
                'verified_by' => $actor->id,
                'verified_at' => now(),
                'reject_reason' => $reason,
            ]);

            $invoice->update(['status' => $invoice->statusWhenUnpaid()]);

            activity('payment')->performedOn($payment)->causedBy($actor)->event('rejected')
                ->withProperties(['invoice' => $invoice->number, 'reason' => $reason])
                ->log('Pembayaran ditolak');

            return $payment;
        });
    }

    /**
     * FR-PAY-05 (P1): owner membatalkan verifikasi yang keliru. Tidak menghapus data.
     */
    public function revokeVerification(Payment $payment, User $owner, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $owner, $reason) {
            $invoice = $this->lockInvoice($payment->invoice);
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Verified) {
                throw new BusinessRuleException('Hanya pembayaran terverifikasi yang bisa dibatalkan.');
            }

            $payment->update(['status' => PaymentStatus::Rejected, 'reject_reason' => 'Verifikasi dibatalkan: '.$reason]);
            $invoice->update(['status' => $invoice->statusWhenUnpaid(), 'paid_at' => null]);
            $invoice->lease()->lockForUpdate()->first()->decrement('paid_amount', $payment->amount);

            activity('payment')->performedOn($payment)->causedBy($owner)->event('verification_revoked')
                ->withProperties(['invoice' => $invoice->number, 'amount' => $payment->amount, 'reason' => $reason])
                ->log('Verifikasi pembayaran dibatalkan');

            return $payment;
        });
    }

    public function proofExists(Payment $payment): bool
    {
        return $payment->hasProof() && Storage::disk(self::PROOF_DISK)->exists($payment->proof_path);
    }

    private function markVerified(Payment $payment, Invoice $invoice, User $actor): void
    {
        $payment->update([
            'status' => PaymentStatus::Verified,
            'verified_by' => $actor->id,
            'verified_at' => now(),
        ]);

        $invoice->update(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);
        $invoice->lease()->lockForUpdate()->first()->increment('paid_amount', $payment->amount);
    }

    private function lockInvoice(Invoice $invoice): Invoice
    {
        return Invoice::whereKey($invoice->id)->with('lease')->lockForUpdate()->firstOrFail();
    }

    private function assertPayable(Invoice $invoice, int $amount): void
    {
        if (! $invoice->isPayable()) {
            throw new BusinessRuleException('Tagihan '.$invoice->number.' tidak bisa dibayar (status: '.strtolower($invoice->status->label()).').');
        }

        if ($invoice->payments()->where('status', PaymentStatus::Pending)->exists()) {
            throw new BusinessRuleException('Masih ada pembayaran yang menunggu verifikasi untuk tagihan ini.');
        }

        if ($amount !== $invoice->amount) { // BR-06
            throw new BusinessRuleException('Nominal harus sama dengan tagihan: '.rupiah($invoice->amount).'.');
        }
    }
}
