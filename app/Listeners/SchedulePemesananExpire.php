<?php

namespace App\Listeners;

use Illuminate\Console\Scheduling\Schedule;

class SchedulePemesananExpire
{
    /**
     * Handle the event.
     */
    public function handle(Schedule $schedule): void
    {
        // Jalan tiap 1 menit
        $schedule->command('pemesanan:expire')->everyMinute();
    }
}
