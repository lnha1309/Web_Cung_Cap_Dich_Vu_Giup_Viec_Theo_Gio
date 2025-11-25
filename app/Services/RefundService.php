<?php

namespace App\Services;

use App\Models\DonDat;
use App\Models\LichSuThanhToan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RefundService
{
    /**
     * Main refund handler
     * 
     * @param DonDat $booking
     * @param string $reason - 'user_cancel', 'auto_cancel_2h', etc.
     * @return array ['success' => bool, 'amount' => float, 'message' => string, 'payment_method' => string]
     */
    public function refundOrder($booking, $reason = 'user_cancel')
    {
        // Find successful payment transaction
        $payment = LichSuThanhToan::where('ID_DD', $booking->ID_DD)
            ->where('TrangThai', 'ThanhCong')
            ->where('LoaiGiaoDich', 'payment')
            ->first();

        if (!$payment) {
            return [
                'success' => true,
                'amount' => 0,
                'message' => 'Không tìm thấy giao dịch thanh toán',
                'payment_method' => 'unknown'
            ];
        }

        $paymentMethod = $payment->PhuongThucThanhToan;
        $refundAmount = 0;

        // Calculate refund amount based on order type
        if ($booking->LoaiDon === 'hour') {
            // Hourly: full refund
            $refundAmount = $booking->TongTienSauGiam;
        } else {
            // Monthly: 80% of unfinished sessions
            $totalSessions = $booking->lichBuoiThang->count();
            $completedSessions = $booking->lichBuoiThang->where('TrangThaiBuoi', 'completed')->count();
            $unfinishedSessions = $totalSessions - $completedSessions;

            if ($totalSessions > 0) {
                $refundAmount = ($booking->TongTienSauGiam / $totalSessions) * $unfinishedSessions * 0.8;
            }
        }

        // Only process VNPay refunds
        if ($paymentMethod === 'VNPay' && $payment->MaGiaoDichVNPAY && $refundAmount > 0) {
            $refundResult = $this->callVnpayRefund($booking, $payment, $refundAmount, $reason);

            if (!$refundResult['success']) {
                return [
                    'success' => false,
                    'amount' => 0,
                    'message' => $refundResult['message'],
                    'payment_method' => $paymentMethod
                ];
            }

            // Log refund transaction
            $this->logRefundTransaction($booking, $payment, $refundAmount, $reason);

            return [
                'success' => true,
                'amount' => $refundAmount,
                'message' => 'Hoàn tiền thành công qua VNPay',
                'payment_method' => $paymentMethod
            ];
        }

        // Cash payment or no refund needed
        return [
            'success' => true,
            'amount' => 0,
            'message' => $paymentMethod === 'TienMat' ? 'Thanh toán bằng tiền mặt' : 'Không cần hoàn tiền',
            'payment_method' => $paymentMethod
        ];
    }

    /**
     * Call VNPay refund API
     */
    private function callVnpayRefund($booking, $payment, $refundAmount, $reason)
    {
        $requestId = (string) Str::uuid();
        $createDate = now()->format('YmdHis');
        $amount = (int) round($refundAmount * 100);

        // Log for debugging
        Log::info('VNPay Refund Request', [
            'booking_id' => $booking->ID_DD,
            'reason' => $reason,
            'original_amount' => $booking->TongTienSauGiam,
            'refund_amount' => $refundAmount,
            'vnp_amount' => $amount,
            'payment_transaction_no' => $payment->MaGiaoDichVNPAY,
        ]);

        // Validate refund amount
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Số tiền hoàn phải lớn hơn 0',
            ];
        }

        if ($refundAmount > $booking->TongTienSauGiam) {
            return [
                'success' => false,
                'message' => 'Số tiền hoàn không được lớn hơn số tiền đã thanh toán',
            ];
        }

        // Determine transaction type: 02 for full refund, 03 for partial refund
        $transactionType = ($refundAmount >= $booking->TongTienSauGiam) ? '02' : '03';

        $payload = [
            'vnp_RequestId' => $requestId,
            'vnp_Version' => config('vnpay.version'),
            'vnp_Command' => 'refund',
            'vnp_TmnCode' => config('vnpay.tmn_code'),
            'vnp_TransactionType' => $transactionType,
            'vnp_TxnRef' => $booking->ID_DD,
            'vnp_Amount' => $amount,
            'vnp_TransactionNo' => $payment->MaGiaoDichVNPAY,
            'vnp_TransactionDate' => $payment->ThoiGian ? Carbon::parse($payment->ThoiGian)->format('YmdHis') : $createDate,
            'vnp_CreateBy' => 'system_' . $reason,
            'vnp_CreateDate' => $createDate,
            'vnp_IpAddr' => request()->ip() ?? '127.0.0.1',
            'vnp_OrderInfo' => 'Hoan tien don hang ' . $booking->ID_DD . ' - ' . $reason,
        ];

        $data = implode('|', [
            $payload['vnp_RequestId'],
            $payload['vnp_Version'],
            $payload['vnp_Command'],
            $payload['vnp_TmnCode'],
            $payload['vnp_TransactionType'],
            $payload['vnp_TxnRef'],
            $payload['vnp_Amount'],
            $payload['vnp_TransactionNo'],
            $payload['vnp_TransactionDate'],
            $payload['vnp_CreateBy'],
            $payload['vnp_CreateDate'],
            $payload['vnp_IpAddr'],
            $payload['vnp_OrderInfo'],
        ]);

        $secretKey = config('vnpay.hash_secret');
        $payload['vnp_SecureHash'] = hash_hmac('sha512', $data, $secretKey);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(config('vnpay.refund_url'), $payload);

            $body = $response->json();

            Log::info('VNPay Refund Response', [
                'booking_id' => $booking->ID_DD,
                'response' => $body
            ]);

            if (($body['vnp_ResponseCode'] ?? null) === '00') {
                return ['success' => true];
            }

            return [
                'success' => false,
                'message' => $body['vnp_Message'] ?? 'Lỗi không xác định',
            ];
        } catch (\Exception $e) {
            Log::error('VNPay Refund Exception', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi kết nối VNPay: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Log refund transaction to database
     */
    private function logRefundTransaction($booking, $originalPayment, $refundAmount, $reason)
    {
        LichSuThanhToan::create([
            'ID_LSTT' => \App\Support\IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
            'PhuongThucThanhToan' => 'VNPay',
            'TrangThai' => 'ThanhCong',
            'SoTienThanhToan' => $refundAmount,
            'ID_DD' => $booking->ID_DD,
            'LoaiGiaoDich' => 'refund',
            'LyDoHoanTien' => $reason,
            'MaGiaoDichGoc' => $originalPayment->MaGiaoDichVNPAY,
        ]);

        Log::info('Refund transaction logged', [
            'booking_id' => $booking->ID_DD,
            'amount' => $refundAmount,
            'reason' => $reason
        ]);
    }
}
