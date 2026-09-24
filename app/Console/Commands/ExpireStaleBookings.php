<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireStaleBookings extends Command
{
    protected $signature = 'bookings:expire-stale';

    protected $description = 'Kedaluwarsakan pengajuan sewa yang terlalu lama menunggu (FR-BOOK-07)';

    public function handle(BookingService $bookings): int
    {
        $count = $bookings->expireStale();
        $this->info("{$count} pengajuan sewa kedaluwarsa.");

        return self::SUCCESS;
    }
}
