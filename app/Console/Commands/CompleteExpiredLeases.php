<?php

namespace App\Console\Commands;

use App\Services\LeaseService;
use Illuminate\Console\Command;

class CompleteExpiredLeases extends Command
{
    protected $signature = 'leases:complete-expired';

    protected $description = 'Selesaikan kontrak aktif yang sudah melewati tanggal akhir (FR-LEASE-06)';

    public function handle(LeaseService $leases): int
    {
        $count = $leases->completeExpired();
        $this->info("{$count} kontrak diselesaikan.");

        return self::SUCCESS;
    }
}
