<?php

namespace App\Console\Commands;

use App\Models\LichLamViec;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshStaffSchedules extends Command
{
    protected $signature = 'staff:schedules:refresh';

    protected $description = 'Lam moi lich dang ky tuan toi (xoa ca ready va giu ca assigned)';

    public function handle(): int
    {
        $today = Carbon::today();
        // Next Monday (start of next week)
        $nextMonday = $today->copy()->startOfWeek(Carbon::MONDAY)->addWeek();

        // Run only 3 days before that Monday (e.g. Fri if Mon is week start)
        if ($today->diffInDays($nextMonday) !== 3) {
            $this->info('Khong trong ngay lam moi (con ' . $today->diffInDays($nextMonday) . ' ngay). Bo qua.');
            return self::SUCCESS;
        }

        $weekStart = $nextMonday->copy();
        $weekEnd = $nextMonday->copy()->endOfWeek(Carbon::SUNDAY);

        $this->info('Lam moi lich ready tu ' . $weekStart->toDateString() . ' den ' . $weekEnd->toDateString());

        $count = 0;

        DB::transaction(function () use ($weekStart, $weekEnd, &$count) {
            $count = LichLamViec::whereBetween('NgayLam', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('TrangThai', 'ready')
                ->delete();
        });

        $this->info('Da xoa ' . $count . ' ca ready cho tuan toi. Ca assigned duoc giu nguyen.');

        return self::SUCCESS;
    }
}
