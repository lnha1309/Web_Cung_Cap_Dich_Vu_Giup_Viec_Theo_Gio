<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\AutoCancelOrdersJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Trong giai đoạn dev: cho chạy mỗi phút cho dễ test
        $schedule->call(function () {
            // Chạy job ngay lập tức, không đưa vào queue
            AutoCancelOrdersJob::dispatchSync();
        })->everyMinute();

        // Sau khi ổn rồi có thể đổi lại:
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
