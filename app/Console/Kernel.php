<?php

namespace App\Console;

use App\Jobs\AutoCancelOrdersJob;
use App\Jobs\AutoCompleteOrdersJob;
use App\Jobs\NotifyFindingStaffDelayJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Dev: run every minute for easy testing (force sync connection so queue worker không cần chạy)
        $schedule->job(new AutoCancelOrdersJob())->everyMinute()->onConnection('sync');
        $schedule->job(new AutoCompleteOrdersJob())->everyMinute()->onConnection('sync');
        $schedule->job(new NotifyFindingStaffDelayJob())->everyMinute()->onConnection('sync');

        // Once stable, consider slowing down:
        // })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
