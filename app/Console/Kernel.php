<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $logFile = storage_path('logs/sync.log');
        $schedule->command('sync:all')
            ->dailyAt('12:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path($logFile));

        $schedule->command('sync:all')
            ->dailyAt('18:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path($logFile));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
