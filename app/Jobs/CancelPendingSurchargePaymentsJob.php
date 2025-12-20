<?php

namespace App\Jobs;

use App\Models\DonDat;
use App\Models\LichSuThanhToan;
use App\Models\ChiTietPhuThu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job to cancel pending surcharge VNPay payments that have timed out (5 minutes).
 * When a surcharge payment times out, we:
 * 1. Mark the payment as 'ThatBai' (failed)
 * 2. Remove the surcharge record (ChiTietPhuThu PT001)
 * 3. Revert the surcharge amount from order total
 * 4. Reset RescheduleCount so user can choose again
 */
class CancelPendingSurchargePaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now();
        Log::info('CancelPendingSurchargePaymentsJob started', ['time' => $now->toDateTimeString()]);

        // Find pending reschedule surcharge payments older than 5 minutes
        $pendingPayments = LichSuThanhToan::where('PhuongThucThanhToan', 'VNPay')
            ->where('TrangThai', 'ChoXuLy')
            ->where('LoaiGiaoDich', 'reschedule_surcharge')
            ->where('ThoiGian', '<', $now->copy()->subMinutes(5))
            ->get();

        $processed = 0;

        foreach ($pendingPayments as $payment) {
            try {
                DB::beginTransaction();

                $orderId = $payment->ID_DD;

                // 1. Mark payment as failed
                $payment->TrangThai = 'ThatBai';
                $payment->GhiChu = ($payment->GhiChu ?? '') . ' - Timeout 5 phut, cho phep chon lai';
                $payment->save();

                Log::info('CancelPendingSurchargePaymentsJob - Payment marked as failed', [
                    'payment_id' => $payment->ID_LSTT,
                    'order_id' => $orderId,
                ]);

                // 2. Order was NOT updated before payment, so nothing to revert
                // Just log for clarity
                $order = DonDat::find($orderId);
                if ($order) {
                    // Reset FindingStaffResponse to allow user to try again
                    // (in case it was changed, though normally it shouldn't be for VNPay)
                    if ($order->FindingStaffResponse === 'reschedule') {
                        $order->FindingStaffResponse = 'pending';
                        $order->save();
                    }
                    
                    Log::info('CancelPendingSurchargePaymentsJob - User can try reschedule again', [
                        'order_id' => $orderId,
                    ]);
                }

                DB::commit();
                $processed++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('CancelPendingSurchargePaymentsJob failed for payment', [
                    'payment_id' => $payment->ID_LSTT ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('CancelPendingSurchargePaymentsJob completed', [
            'time' => Carbon::now()->toDateTimeString(),
            'candidates' => $pendingPayments->count(),
            'processed' => $processed,
        ]);
    }
}
