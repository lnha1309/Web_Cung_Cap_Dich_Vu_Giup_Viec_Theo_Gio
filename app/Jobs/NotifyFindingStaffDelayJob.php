<?php

namespace App\Jobs;

use App\Models\DonDat;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyFindingStaffDelayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService): void
    {
        $now = Carbon::now();
        Log::info('NotifyFindingStaffDelayJob started', ['time' => $now->toDateTimeString()]);

        $orders = DonDat::where('LoaiDon', 'hour')
            ->where('TrangThaiDon', 'finding_staff')
            ->whereNull('FindingStaffResponse')
            ->whereNull('FindingStaffPromptSentAt')
            ->whereNotNull('NgayLam')
            ->whereNotNull('GioBatDau')
            ->get();

        $processed = 0;

        foreach ($orders as $order) {
            try {
                $createdAt = $order->NgayTao ? Carbon::parse($order->NgayTao) : null;
                $startAt = Carbon::parse($order->NgayLam . ' ' . $order->GioBatDau);

                if (!$createdAt || $startAt->lessThanOrEqualTo($createdAt)) {
                    continue;
                }

                // Calculate threshold: notify when 1/3 of time has passed
                // Example: Created at 14:52, Start at 20:52 (6 hours)
                // Threshold = 14:52 + (6h / 3) = 14:52 + 2h = 16:52
                $diffSeconds = $startAt->diffInSeconds($createdAt);
                $thresholdAt = $createdAt->copy()->addSeconds((int) ceil($diffSeconds / 3));

                // Only notify if current time has reached the threshold
                if ($now->lessThan($thresholdAt)) {
                    continue; // Not time yet
                }

                $order->FindingStaffPromptSentAt = $now;
                $order->FindingStaffResponse = 'pending';
                $order->save();

                $notificationService->notifyFindingStaffDelay($order, $thresholdAt);
                $processed++;
            } catch (\Exception $e) {
                Log::error('NotifyFindingStaffDelayJob failed', [
                    'order_id' => $order->ID_DD ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('NotifyFindingStaffDelayJob completed', [
            'time' => Carbon::now()->toDateTimeString(),
            'candidates' => $orders->count(),
            'notified' => $processed,
        ]);
    }
}
