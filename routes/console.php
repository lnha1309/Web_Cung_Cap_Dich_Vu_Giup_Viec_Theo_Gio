<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AutoCancelOrdersJob;
use App\Jobs\AutoCompleteOrdersJob;
use App\Jobs\NotifyFindingStaffDelayJob;
use App\Jobs\CancelPendingVnpayBookingsJob;
use App\Jobs\CancelPendingSurchargePaymentsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto jobs: chạy mỗi phút (dev/test) trên connection sync để không cần queue worker
Schedule::call(function () {
    AutoCancelOrdersJob::dispatchSync();
    AutoCompleteOrdersJob::dispatchSync();
    NotifyFindingStaffDelayJob::dispatchSync();
    CancelPendingVnpayBookingsJob::dispatchSync();
    CancelPendingSurchargePaymentsJob::dispatchSync();
})->everyMinute();

// Sau này nếu muốn 5 phút 1 lần thì đổi thành:
// })->everyFiveMinutes();
