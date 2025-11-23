<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        App\Console\Commands\RefreshStaffSchedules::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Lam moi lich dang ky tuan toi 3 ngay truoc thu 2 (chay 02:00 hang ngay)
        $schedule->command('staff:schedules:refresh')->dailyAt('02:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
