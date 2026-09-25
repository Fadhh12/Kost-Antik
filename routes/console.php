<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Jadwal harian (SRS 2.4), zona waktu Asia/Jakarta
|--------------------------------------------------------------------------
| Jalankan scheduler: php artisan schedule:work (dev) atau cron
| "* * * * * php artisan schedule:run" (produksi).
*/

Schedule::command('invoices:mark-overdue')->dailyAt('00:05')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('leases:complete-expired')->dailyAt('00:10')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('bookings:expire-stale')->dailyAt('00:15')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('backup:run')->dailyAt('02:00')->timezone('Asia/Jakarta')->withoutOverlapping();
