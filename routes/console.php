<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AutoCancelOrdersJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🔔 Auto-cancel orders job – chạy mỗi phút (dev/test)
Schedule::call(function () {
    AutoCancelOrdersJob::dispatchSync();
})->everyMinute();

// Sau này nếu muốn 5 phút 1 lần thì đổi thành:
// })->everyFiveMinutes();
