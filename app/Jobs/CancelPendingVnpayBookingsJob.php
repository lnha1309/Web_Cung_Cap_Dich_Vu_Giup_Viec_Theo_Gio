<?php

namespace App\Jobs;

use App\Models\DonDat;
use App\Models\LichSuThanhToan;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job to cancel VNPay bookings where payment was not completed within 5 minutes.
 * When a booking payment times out, we:
 * 1. Mark payment as 'ThatBai' (failed)
 * 2. Update order status to 'cancelled' 
 * 3. Send cancellation notification to customer
 * Note: No refund needed since payment was never completed
 */
class CancelPendingVnpayBookingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService): void
    {
        $now = Carbon::now();
        Log::info('CancelPendingVnpayBookingsJob started', ['time' => $now->toDateTimeString()]);

        // Find VNPay payments for regular bookings that are still pending after 5 minutes
        // (LoaiGiaoDich is NULL or 'payment', not 'reschedule_surcharge')
        $pendingPayments = LichSuThanhToan::where('PhuongThucThanhToan', 'VNPay')
            ->where('TrangThai', 'ChoXuLy')
            ->where(function ($query) {
                $query->whereNull('LoaiGiaoDich')
                      ->orWhere('LoaiGiaoDich', 'payment');
            })
            ->where('ThoiGian', '<', $now->copy()->subMinutes(5))
            ->whereNotNull('ID_DD')
            ->get();

        $processed = 0;

        foreach ($pendingPayments as $payment) {
            try {
                DB::beginTransaction();

                $orderId = $payment->ID_DD;
                $order = DonDat::with('dichVu', 'khachHang')->find($orderId);

                if (!$order) {
                    // Payment exists but order doesn't - just mark payment as failed
                    $payment->TrangThai = 'ThatBai';
                    $payment->GhiChu = ($payment->GhiChu ?? '') . ' - Order not found, payment timeout';
                    $payment->save();
                    DB::commit();
                    $processed++;
                    continue;
                }

                // Only cancel if payment not completed (MaGiaoDichVNPAY is null)
                if ($payment->MaGiaoDichVNPAY !== null) {
                    // Payment was actually completed, skip
                    DB::commit();
                    continue;
                }

                // Skip if order is already cancelled
                if ($order->TrangThaiDon === 'cancelled') {
                    $payment->TrangThai = 'ThatBai';
                    $payment->GhiChu = ($payment->GhiChu ?? '') . ' - Order already cancelled';
                    $payment->save();
                    DB::commit();
                    $processed++;
                    continue;
                }

                // 1. Mark payment as failed
                $payment->TrangThai = 'ThatBai';
                $payment->GhiChu = ($payment->GhiChu ?? '') . ' - Timeout 5 phut, don bi huy';
                $payment->save();

                Log::info('CancelPendingVnpayBookingsJob - Payment marked as failed', [
                    'payment_id' => $payment->ID_LSTT,
                    'order_id' => $orderId,
                ]);

                // 2. Update order status to cancelled (NOT delete)
                $order->TrangThaiDon = 'cancelled';
                $order->save();

                Log::info('CancelPendingVnpayBookingsJob - Order cancelled', [
                    'order_id' => $orderId,
                ]);

                // 3. Send notification to customer
                // No refund needed since payment was never completed
                $notificationService->notifyOrderCancelled($order, 'payment_timeout', [
                    'payment_method' => 'VNPay',
                    'amount' => 0, // No refund since payment wasn't completed
                ]);

                DB::commit();
                $processed++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('CancelPendingVnpayBookingsJob failed for payment', [
                    'payment_id' => $payment->ID_LSTT ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('CancelPendingVnpayBookingsJob completed', [
            'time' => Carbon::now()->toDateTimeString(),
            'candidates' => $pendingPayments->count(),
            'processed' => $processed,
        ]);
    }
}
