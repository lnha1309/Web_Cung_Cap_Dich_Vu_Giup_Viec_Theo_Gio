<?php

namespace App\Jobs;

use App\Models\DonDat;
use App\Models\LichBuoiThang;
use App\Models\LichSuThanhToan;
use App\Services\NotificationService;
use App\Services\StaffWalletService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCompleteOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, StaffWalletService $walletService): void
    {
        Log::info('AutoCompleteOrdersJob started', ['time' => now()]);

        $completed = $this->completeHourlyOrders($notificationService, $walletService);

        Log::info('AutoCompleteOrdersJob completed', [
            'time' => now(),
            'completed_count' => $completed,
        ]);
    }

    /**
     * Auto-complete hourly orders that ended > 1h ago but not marked done.
     */
    private function completeHourlyOrders(
        NotificationService $notificationService,
        StaffWalletService $walletService
    ): int {
        $count = 0;
        $now = Carbon::now();

        $orders = DonDat::where('LoaiDon', 'hour')
            ->whereIn('TrangThaiDon', ['assigned', 'confirmed'])
            ->whereNotNull('NgayLam')
            ->whereNotNull('GioBatDau')
            ->whereNotNull('ThoiLuongGio')
            ->whereNotNull('ID_NV')
            ->get();

        foreach ($orders as $order) {
            try {
                $endTime = Carbon::parse($order->NgayLam . ' ' . $order->GioBatDau)
                    ->addHours((float) $order->ThoiLuongGio);
                $autoCompleteTime = $endTime->copy()->addHour();

                if ($now->gte($autoCompleteTime)) {
                    $this->finalizeOrder($order, $notificationService, $walletService);
                    $count++;
                }
            } catch (\Exception $e) {
                Log::error('AutoCompleteOrdersJob failed to process order', [
                    'order_id' => $order->ID_DD ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Mirror staff "complete" flow: set status to done, notify, and handle wallet.
     */
    private function finalizeOrder(
        DonDat $order,
        NotificationService $notificationService,
        StaffWalletService $walletService
    ): void {
        if (in_array($order->TrangThaiDon, ['completed', 'done', 'cancelled'], true)) {
            return;
        }

        $staff = $order->nhanVien;
        if (!$staff) {
            Log::warning('AutoCompleteOrdersJob skipped order without staff', [
                'order_id' => $order->ID_DD,
            ]);
            return;
        }

        $oldStatus = $order->TrangThaiDon;
        $order->TrangThaiDon = 'completed';
        $order->save();

        // Notify customer about status change
        try {
            $notificationService->notifyOrderStatusChanged($order, $oldStatus, 'completed');
        } catch (\Exception $e) {
            Log::error('AutoCompleteOrdersJob notification failed', [
                'order_id' => $order->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // Wallet settlement (same logic as manual complete)
        $payment = LichSuThanhToan::where('ID_DD', $order->ID_DD)
            ->where('TrangThai', 'ThanhCong')
            ->orderByDesc('ThoiGian')
            ->first();

        $orderAmount = (float) ($order->TongTien ?? $order->TongTienSauGiam ?? 0);
        if ($order->LoaiDon === 'month') {
            $sessionCount = LichBuoiThang::where('ID_DD', $order->ID_DD)->count();
            $sessionCount = max(1, $sessionCount);
            $orderAmount = $orderAmount / $sessionCount;
        }

        if ($orderAmount > 0) {
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';
            $normalizedMethod = $paymentMethod === 'TienMat' ? 'cash' : 'online';

            if (!$walletService->hasOrderTransaction(
                $order->ID_DD,
                ['cash_commission', 'order_credit']
            )) {
                if ($normalizedMethod === 'cash') {
                    $commission = -1 * round($orderAmount * 0.2, 2);
                    $walletService->applyChange($staff, $commission, 'cash_commission', [
                        'description' => 'Tru 20% don tien mat ' . $order->ID_DD,
                        'order_id' => $order->ID_DD,
                        'source' => 'cash',
                    ]);
                } else {
                    $credit = round($orderAmount * 0.8, 2);
                    $walletService->applyChange($staff, $credit, 'order_credit', [
                        'description' => 'Cong 80% don thanh toan online ' . $order->ID_DD,
                        'order_id' => $order->ID_DD,
                        'source' => strtolower((string) $paymentMethod),
                    ]);
                }
            }
        }
    }
}
