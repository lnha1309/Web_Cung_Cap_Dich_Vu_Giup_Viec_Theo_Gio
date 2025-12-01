<?php

namespace App\Jobs;

use App\Models\DonDat;
use App\Models\LichBuoiThang;
use App\Services\RefundService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoCancelOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $refundService;
    private $notificationService;

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(RefundService $refundService, NotificationService $notificationService)
    {
        $this->refundService = $refundService;
        $this->notificationService = $notificationService;

        Log::info('AutoCancelOrdersJob started', ['time' => now()]);

        $cancelledCount = 0;

        // Process hourly orders
        $cancelledCount += $this->cancelHourlyOrders();

        // Process monthly orders
        $cancelledCount += $this->cancelMonthlyOrders();

        Log::info('AutoCancelOrdersJob completed', [
            'time' => now(),
            'cancelled_count' => $cancelledCount
        ]);
    }

    /**
     * Cancel hourly orders that meet the criteria
     */
    private function cancelHourlyOrders()
{
    $count = 0;

    $orders = DonDat::where('LoaiDon', 'hour')
        ->whereIn('TrangThaiDon', ['assigned', 'finding_staff', 'rejected'])
        ->whereNotNull('NgayLam')
        ->whereNotNull('GioBatDau')
        ->get();

    foreach ($orders as $order) {
        try {
            $startTime = \Carbon\Carbon::parse($order->NgayLam.' '.$order->GioBatDau);
            $cancelCheckTime = $startTime->copy()->subHours(2);
            $now = now();

            $shouldCancel = $now->gte($cancelCheckTime);

            \Log::info('DEBUG AUTO-CANCEL CHECK', [
                'order_id'        => $order->ID_DD,
                'start_time'      => $startTime->toDateTimeString(),
                'cancel_check'    => $cancelCheckTime->toDateTimeString(),
                'now'             => $now->toDateTimeString(),
                'should_cancel'   => $shouldCancel,
            ]);

            // Cancel if we reached/passed the -2h mark, even if the window was missed earlier
            if ($shouldCancel) {
                $this->cancelOrder($order, 'auto_cancel_2h');
                $count++;
            }
        } catch (\Exception $e) {
            \Log::error('Error cancelling hourly order', [
                'order_id' => $order->ID_DD,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    return $count;
}


    /**
     * Cancel monthly sessions that meet the criteria
     */
    private function cancelMonthlyOrders()
    {
        $count = 0;

        // Find monthly sessions that are finding_staff or rejected, have no staff, and are within 2 hours
        $sessions = LichBuoiThang::whereIn('TrangThaiBuoi', ['finding_staff', 'rejected'])
            ->whereNull('ID_NV')
            ->whereHas('donDat', function ($q) {
                $q->where('LoaiDon', 'month')
                  ->where('TrangThaiDon', '!=', 'cancelled');
            })
            ->with('donDat')
            ->get();

        foreach ($sessions as $session) {
            try {
                if ($session->NgayLam && $session->GioBatDau) {
                    $startTime = Carbon::parse($session->NgayLam . ' ' . $session->GioBatDau);
                    $cancelCheckTime = $startTime->copy()->subHours(2);
                    $now = now();

                    $shouldCancel = $now->gte($cancelCheckTime);

                    if ($shouldCancel) {
                        $this->cancelSession($session, 'auto_cancel_2h');
                        $count++;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error cancelling monthly session', [
                    'session_id' => $session->ID_Buoi,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }

    /**
     * Cancel a session and trigger refund + notification
     */
    private function cancelSession($session, $reason)
    {
        DB::beginTransaction();

        try {
            // 1. Update session status
            $session->TrangThaiBuoi = 'cancelled';
            $session->save();

            Log::info('Session auto-cancelled', [
                'session_id' => $session->ID_Buoi,
                'reason' => $reason
            ]);

            // 2. Process refund if applicable
            $refundResult = $this->refundService->refundSession($session, $reason);

            Log::info('Session Refund processed', [
                'session_id' => $session->ID_Buoi,
                'success' => $refundResult['success'],
                'amount' => $refundResult['amount'],
                'payment_method' => $refundResult['payment_method']
            ]);

            // 3. Send notification to customer
            $this->notificationService->notifySessionCancelled($session, $reason, $refundResult);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in cancelSession transaction', [
                'session_id' => $session->ID_Buoi,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Cancel an order and trigger refund + notification
     */
    private function cancelOrder($order, $reason)
    {
        DB::beginTransaction();

        try {
            // 1. Update order status
            $order->TrangThaiDon = 'cancelled';
            $order->save();

            Log::info('Order auto-cancelled', [
                'order_id' => $order->ID_DD,
                'reason' => $reason,
                'order_type' => $order->LoaiDon
            ]);

            // 2. Process refund if applicable
            $refundResult = $this->refundService->refundOrder($order, $reason);

            Log::info('Refund processed', [
                'order_id' => $order->ID_DD,
                'success' => $refundResult['success'],
                'amount' => $refundResult['amount'],
                'payment_method' => $refundResult['payment_method']
            ]);

            // 3. Send notification to customer
            $this->notificationService->notifyOrderCancelled($order, $reason, $refundResult);

            DB::commit();

            Log::info('Order cancellation completed successfully', [
                'order_id' => $order->ID_DD
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in cancelOrder transaction', [
                'order_id' => $order->ID_DD,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
