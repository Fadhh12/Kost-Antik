<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Lease;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class InvoiceService
{
    public function __construct(private CodeGenerator $codes) {}

    /**
     * FR-INV-01/02 + BR-05: satu invoice per bulan, dibuat penuh di awal kontrak.
     *
     * @return Collection<int, Invoice>
     */
    public function generateForLease(Lease $lease): Collection
    {
        $graceDays = (int) config('kost.grace_days');
        $invoices = collect();

        for ($n = 1; $n <= $lease->duration_months; $n++) {
            // Hitung dari start_date agar tidak bergeser (mis. mulai tanggal 31).
            $periodStart = $lease->start_date->copy()->addMonthsNoOverflow($n - 1);
            $periodEnd = $lease->start_date->copy()->addMonthsNoOverflow($n)->subDay();
            $due = $periodStart->copy()->addDays($graceDays);

            $invoices->push($lease->invoices()->create([
                'number' => $this->codes->invoiceNumber($periodStart),
                'sequence' => $n,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'due_date' => $due,
                'amount' => $lease->monthly_price, // FR-INV-06
                'status' => today()->gt($due) ? InvoiceStatus::Overdue : InvoiceStatus::Unpaid,
            ]));
        }

        return $invoices;
    }

    /**
     * FR-INV-05: invoice belum dibayar yang lewat jatuh tempo menjadi overdue.
     */
    public function markOverdue(): int
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Unpaid)
            ->whereDate('due_date', '<', today())
            ->update(['status' => InvoiceStatus::Overdue, 'updated_at' => now()]);
    }

    /**
     * FR-LEASE-07: batalkan invoice belum dibayar yang periodenya dimulai setelah tanggal tertentu.
     */
    public function voidAfter(Lease $lease, CarbonInterface $date): int
    {
        return $lease->invoices()
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::Overdue])
            ->whereDate('period_start', '>', $date)
            ->update(['status' => InvoiceStatus::Void, 'updated_at' => now()]);
    }
}
