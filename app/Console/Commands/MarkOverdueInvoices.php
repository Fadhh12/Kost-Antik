<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Tandai tagihan belum dibayar yang lewat jatuh tempo sebagai terlambat (FR-INV-05)';

    public function handle(InvoiceService $invoices): int
    {
        $count = $invoices->markOverdue();
        $this->info("{$count} tagihan ditandai terlambat.");

        return self::SUCCESS;
    }
}
