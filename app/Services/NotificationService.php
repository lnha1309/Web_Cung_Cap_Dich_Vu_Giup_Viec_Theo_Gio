<?php

namespace App\Services;

use App\Models\KhachHang;
use App\Models\TaiKhoan;
use App\Models\ThongBao;
use App\Models\DonDat;
use App\Mail\OrderStatusMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Notify customer about new order creation
     */
    public function notifyOrderCreated($booking)
    {
        $serviceName = $booking->dichVu->TenDV ?? 'dịch vụ';
        $startTime = $this->getFormattedStartTime($booking);
        $statusText = $this->getStatusText($booking->TrangThaiDon);

        $notification = ThongBao::create([
            'ID_TB' => \App\Support\IdGenerator::next('ThongBao', 'ID_TB', 'TB_'),
            'ID_KH' => $booking->ID_KH,
            'TieuDe' => 'Đặt đơn thành công',
            'NoiDung' => "Bạn đã đặt đơn dịch vụ {$serviceName} vào lúc {$startTime}. Trạng thái hiện tại: {$statusText}",
            'LoaiThongBao' => 'order_created',
            'DaDoc' => false,
            'DuLieuLienQuan' => [
                'ID_DD' => $booking->ID_DD,
                'service_name' => $serviceName,
                'start_time' => $startTime,
            ],
        ]);

        Log::info('Notification created: Order placed', [
            'notification_id' => $notification->ID_TB,
            'customer_id' => $booking->ID_KH,
            'booking_id' => $booking->ID_DD,
        ]);

        $this->sendOrderEmail($booking, 'created');
        $this->pushToCustomer(
            $booking,
            'Đặt đơn thành công',
            'Đơn ' . $booking->ID_DD . ' đã được tạo, vui lòng chờ cập nhật tiếp theo.'
        );

        return $notification;
    }

    /**
     * Notify customer that no staff has been found after the waiting threshold.
     */
    public function notifyFindingStaffDelay($booking, Carbon $triggerAt)
    {
        $startTime = $this->getFormattedStartTime($booking);
        $waitMinutes = null;
        try {
            // Calculate how long we've been waiting (from order creation to now)
            $createdAt = Carbon::parse($booking->NgayTao);
            $waitMinutes = (int) $createdAt->diffInMinutes(Carbon::now());
        } catch (\Exception $e) {
            $waitMinutes = null;
        }

        $notification = ThongBao::create([
            'ID_TB' => \App\Support\IdGenerator::next('ThongBao', 'ID_TB', 'TB_'),
            'ID_KH' => $booking->ID_KH,
            'TieuDe' => 'Chua tim duoc nhan vien',
            'NoiDung' => "Don #{$booking->ID_DD} chua tim duoc nhan vien sau " . ($waitMinutes !== null ? "{$waitMinutes} phut" : 'mot thoi gian') . ". Hay chon doi gio, huy don hoac tiep tuc cho.",
            'LoaiThongBao' => 'finding_staff_delay',
            'DaDoc' => false,
            'DuLieuLienQuan' => [
                'ID_DD' => $booking->ID_DD,
                'start_time' => $startTime,
                'detail_url' => route('bookings.detail', $booking->ID_DD),
                'action' => 'finding_staff_delay',
            ],
        ]);

        $this->pushToCustomer(
            $booking,
            'Chon cach xu ly don ' . $booking->ID_DD,
            'Don chua co nhan vien, vui long chon doi gio/huy/tiep tuc cho.'
        );

        return $notification;
    }

    /**
     * Notify customer after a booking is rescheduled.
     */
    public function notifyOrderRescheduled($booking)
    {
        $startTime = $this->getFormattedStartTime($booking);
        $amount = number_format($booking->TongTienSauGiam ?? $booking->TongTien ?? 0, 0, ',', '.');

        $notification = ThongBao::create([
            'ID_TB' => \App\Support\IdGenerator::next('ThongBao', 'ID_TB', 'TB_'),
            'ID_KH' => $booking->ID_KH,
            'TieuDe' => 'Doi gio bat dau thanh cong',
            'NoiDung' => "Don #{$booking->ID_DD} da doi sang {$startTime}. Tong tien moi: {$amount} d.",
            'LoaiThongBao' => 'order_rescheduled',
            'DaDoc' => false,
            'DuLieuLienQuan' => [
                'ID_DD' => $booking->ID_DD,
                'start_time' => $startTime,
                'amount' => $amount,
            ],
        ]);

        $this->pushToCustomer(
            $booking,
            'Doi gio don ' . $booking->ID_DD,
            "Don da doi sang {$startTime}. Tong tien moi: {$amount} d."
        );

        return $notification;
    }

    /**
     * Notify customer about order cancellation
     * 
     * @param DonDat $booking
     * @param string $cancelType - 'user_cancel' or 'auto_cancel_2h'
     * @param array|null $refundInfo - from RefundService response
     */
    public function notifyOrderCancelled($booking, $cancelType = 'user_cancel', $refundInfo = null)
    {
        $reason = $this->getCancellationReason($cancelType);
        $paymentMethod = $refundInfo['payment_method'] ?? 'unknown';
        $refundAmount = $refundInfo['amount'] ?? 0;
        
        // Generate message based on payment method
        if ($paymentMethod === 'TienMat') {
            $message = "Đơn của bạn đã bị hủy. Lý do: {$reason}. Thanh toán bằng tiền mặt nên không có hoàn tiền.";
        } elseif ($paymentMethod === 'VNPay' && $refundAmount > 0) {
            $formattedAmount = number_format($refundAmount, 0, ',', '.');
            $message = "Đơn của bạn đã bị hủy. Lý do: {$reason}. Số tiền {$formattedAmount} VNĐ đã được hoàn lại qua VNPay.";
        } else {
            $message = "Đơn của bạn đã bị hủy. Lý do: {$reason}.";
        }

        $notification = ThongBao::create([
            'ID_TB' => \App\Support\IdGenerator::next('ThongBao', 'ID_TB', 'TB_'),
            'ID_KH' => $booking->ID_KH,
            'TieuDe' => 'Đơn hàng đã bị hủy',
            'NoiDung' => $message,
            'LoaiThongBao' => 'order_cancelled',
            'DaDoc' => false,
            'DuLieuLienQuan' => [
                'ID_DD' => $booking->ID_DD,
                'cancel_type' => $cancelType,
                'payment_method' => $paymentMethod,
                'refund_amount' => $refundAmount,
            ],
        ]);

        Log::info('Notification created: Order cancelled', [
            'notification_id' => $notification->ID_TB,
            'customer_id' => $booking->ID_KH,
            'booking_id' => $booking->ID_DD,
            'cancel_type' => $cancelType,
        ]);

        $mailType = $cancelType === 'payment_failed' ? 'failed' : 'cancelled';
        $this->sendOrderEmail($booking, $mailType, [
            'reason' => $reason,
            'refund_amount' => $refundAmount,
            'payment_method' => $paymentMethod,
        ]);
        $this->pushToCustomer(
            $booking,
            'Đơn đã bị hủy',
            "Đơn {$booking->ID_DD} đã bị hủy. Lý do: {$reason}."
        );

        return $notification;
    }

    /**
     * Notify customer about order status change
     */
    public function notifyOrderStatusChanged($booking, $oldStatus, $newStatus)
    {
        $statusMessage = $this->getStatusChangeMessage($oldStatus, $newStatus);
        
        if (!$statusMessage) {
            // No notification for some status changes
            return null;
        }

        $notification = ThongBao::create([
            'ID_TB' => \App\Support\IdGenerator::next('ThongBao', 'ID_TB', 'TB_'),
            'ID_KH' => $booking->ID_KH,
            'TieuDe' => 'Cập nhật trạng thái đơn hàng',
            'NoiDung' => "Đơn #{$booking->ID_DD} của bạn {$statusMessage}",
            'LoaiThongBao' => 'order_status_change',
            'DaDoc' => false,
            'DuLieuLienQuan' => [
                'ID_DD' => $booking->ID_DD,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);

        Log::info('Notification created: Status changed', [
            'notification_id' => $notification->ID_TB,
            'customer_id' => $booking->ID_KH,
            'booking_id' => $booking->ID_DD,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        if ($statusMessage) {
          $this->pushToCustomer(
              $booking,
              'Cập nhật đơn hàng',
              "Đơn {$booking->ID_DD} {$statusMessage}"
          );
        }

        return $notification;
    }

    /**
     * Get unread notification count for customer
     */
    public function getUnreadCount($customerId)
    {
        return ThongBao::where('ID_KH', $customerId)
            ->where('DaDoc', false)
            ->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = ThongBao::find($notificationId);
        
        if ($notification) {
            $notification->DaDoc = true;
            $notification->save();
            return true;
        }
        
        return false;
    }

    /**
     * Mark all notifications as read for a customer
     */
    public function markAllAsRead($customerId)
    {
        return ThongBao::where('ID_KH', $customerId)
            ->where('DaDoc', false)
            ->update(['DaDoc' => true]);
    }

    /**
     * Get notifications for customer (paginated)
     */
    public function getNotifications($customerId, $perPage = 20, $filterUnread = false)
    {
        $query = ThongBao::where('ID_KH', $customerId)
            ->newest();

        if ($filterUnread) {
            $query->unread();
        }

        return $query->paginate($perPage);
    }

    // ==================== Helper Methods ====================

    private function getFormattedStartTime($booking)
    {
        if ($booking->LoaiDon === 'hour') {
            if ($booking->NgayLam && $booking->GioBatDau) {
                return \Carbon\Carbon::parse($booking->NgayLam . ' ' . $booking->GioBatDau)->format('H:i d/m/Y');
            }
        } else {
            if ($booking->NgayBatDauGoi) {
                return \Carbon\Carbon::parse($booking->NgayBatDauGoi)->format('d/m/Y');
            }
        }
        return 'chưa xác định';
    }

    private function getStatusText($status)
    {
        $statusMap = [
            'unpaid' => 'Chưa thanh toán',
            'paid' => 'Đã thanh toán',
            'finding_staff' => 'Đang tìm nhân viên',
            'wait_confirm' => 'Chờ xác nhận',
            'assigned' => 'Đã gán nhân viên',
            'confirmed' => 'Đã xác nhận',
            'rejected' => 'Bị từ chối, đang tìm nhân viên khác',
            'completed' => 'Đã hoàn thành, chờ bạn đánh giá',
            'done' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return $statusMap[$status] ?? $status;
    }

    private function getCancellationReason($cancelType)
    {
        $reasonMap = [
            'user_cancel' => 'Khách hàng tự hủy',
            'auto_cancel_2h' => 'Hệ thống tự động hủy do không tìm được nhân viên trong 2 giờ trước giờ bắt đầu',
            'payment_failed' => 'Thanh toán VNPay thất bại',
        ];

        return $reasonMap[$cancelType] ?? 'Không xác định';
    }

    private function getStatusChangeMessage($oldStatus, $newStatus)
    {
        // Map important status changes to user-friendly messages
        $messages = [
            'finding_staff_assigned' => 'đã được gán nhân viên',
            'paid_finding_staff' => 'đã thanh toán thành công, đang tìm nhân viên',
            'assigned_done' => 'đã hoàn thành',
            'wait_confirm_assigned' => 'đã được xác nhận và gán nhân viên',
            'assigned_confirmed' => 'đã được nhân viên xác nhận',
            'assigned_rejected' => 'bị từ chối, chúng tôi đang tìm nhân viên khác',
            'rejected_confirmed' => 'đã có nhân viên khác nhận và xác nhận',
            'finding_staff_confirmed' => 'đã có nhân viên nhận và xác nhận',
            'confirmed_completed' => 'đã hoàn thành, mời bạn đánh giá',
            'assigned_completed' => 'đã hoàn thành, mời bạn đánh giá',
        ];

        $key = $oldStatus . '_' . $newStatus;
        
        // Only notify for significant status changes
        $importantChanges = ['assigned', 'confirmed', 'rejected', 'completed', 'finding_staff'];
        
        if (isset($messages[$key])) {
            return $messages[$key];
        } elseif (in_array($newStatus, $importantChanges)) {
            return 'đã chuyển sang trạng thái: ' . $this->getStatusText($newStatus);
        }

        return null; // Don't notify for minor changes
    }

    /**
     * Send detailed email about order status.
     */
    private function sendOrderEmail($booking, string $type, array $extra = []): void
    {
        try {
            $booking->loadMissing(['dichVu', 'diaChi', 'khachHang']);
            $customer = $booking->khachHang;
            $email = $customer->Email ?? null;

            if (!$email) {
                return;
            }

            $data = [
                'booking' => $booking,
                'type' => $type,
                'customer_name' => $customer->Ten_KH ?? 'Quý khách',
                'service_name' => $booking->dichVu->TenDV ?? 'Dịch vụ',
                'start_time' => ($type === 'session_cancelled' && isset($extra['session_date'], $extra['session_time'])) 
                                ? $extra['session_time'] . ' ' . $extra['session_date'] 
                                : $this->getFormattedStartTime($booking),
                'address' => $booking->diaChi->DiaChiDayDu ?? null,
                'amount' => ($type === 'session_cancelled' && isset($extra['session_value'])) 
                            ? $extra['session_value'] 
                            : (($type === 'cancelled' || $type === 'failed') && isset($extra['refund_amount']) && $extra['refund_amount'] > 0
                                ? $extra['refund_amount']
                                : ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0)),
                'reason' => $extra['reason'] ?? null,
                'refund_amount' => $extra['refund_amount'] ?? 0,
                'payment_method' => $extra['payment_method'] ?? null,
            ];

            Mail::to($email)->send(new OrderStatusMail($data));
        } catch (\Exception $e) {
            Log::error('Failed to send order email', [
                'booking_id' => $booking->ID_DD ?? null,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function pushToCustomer($booking, string $title, string $body): void
    {
        try {
            $kh = $booking->khachHang ?? KhachHang::find($booking->ID_KH);
            if (!$kh || !$kh->ID_TK) {
                return;
            }
            $account = TaiKhoan::find($kh->ID_TK);
            $playerId = $account?->onesignal_player_id;
            $appId = config('services.onesignal.app_id');
            $apiKey = config('services.onesignal.api_key');
            if (!$playerId || !$appId || !$apiKey) {
                return;
            }

            $payload = [
                'app_id' => $appId,
                'include_player_ids' => [$playerId],
                'headings' => ['en' => $title],
                'contents' => ['en' => $body],
                'data' => [
                    'booking_id' => $booking->ID_DD,
                    'target_role' => 'customer',
                ],
            ];

            Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.onesignal.com/notifications', $payload);
        } catch (\Exception $e) {
            Log::error('Failed to push OneSignal to customer', [
                'booking_id' => $booking->ID_DD ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Notify customer about session cancellation
     * 
     * @param LichBuoiThang $session
     * @param string $reason
     * @param array|null $refundInfo
     */
    public function notifySessionCancelled($session, $reason = 'auto_cancel_session', $refundInfo = null)
    {
        $booking = $session->donDat;
        if (!$booking) return null;

        $paymentMethod = $refundInfo['payment_method'] ?? 'unknown';
        $refundAmount = $refundInfo['amount'] ?? 0;
        $sessionDate = \Carbon\Carbon::parse($session->NgayLam)->format('d/m/Y');
        $sessionTime = \Carbon\Carbon::parse($session->GioBatDau)->format('H:i');
        
        // Generate message
        if ($reason === 'auto_cancel_session') {
            $message = "Buổi làm ngày {$sessionDate} lúc {$sessionTime} đã bị hủy do không tìm được nhân viên.";
        } elseif ($reason === 'user_cancel_session') {
            $message = "Buổi làm ngày {$sessionDate} lúc {$sessionTime} đã bị hủy theo yêu cầu của bạn.";
        } else {
            $message = "Buổi làm ngày {$sessionDate} lúc {$sessionTime} đã bị hủy. Lý do: {$reason}.";
        }

        if (strcasecmp($paymentMethod, 'VNPay') === 0 && $refundAmount > 0) {
            $formattedAmount = number_format($refundAmount, 0, ',', '.');
            $message .= " Số tiền {$formattedAmount} VNĐ đã được hoàn lại qua VNPay.";
        } elseif ($paymentMethod === 'TienMat') {
            $message .= " Thanh toán bằng tiền mặt nên không có hoàn tiền.";
        }

        $notification = ThongBao::create([
            'ID_TB' => \App\Support\IdGenerator::next('ThongBao', 'ID_TB', 'TB_'),
            'ID_KH' => $booking->ID_KH,
            'TieuDe' => 'Hủy buổi làm việc',
            'NoiDung' => $message,
            'LoaiThongBao' => 'session_cancelled',
            'DaDoc' => false,
            'DuLieuLienQuan' => [
                'ID_DD' => $booking->ID_DD,
                'ID_Buoi' => $session->ID_Buoi,
                'reason' => $reason,
                'refund_amount' => $refundAmount,
            ],
        ]);

        Log::info('Notification created: Session cancelled', [
            'notification_id' => $notification->ID_TB,
            'session_id' => $session->ID_Buoi,
            'refund_amount' => $refundAmount,
        ]);

        // Calculate session value for email display
        $totalSessions = $booking->lichBuoiThang->count();
        $sessionValue = $totalSessions > 0 ? ($booking->TongTienSauGiam / $totalSessions) : 0;

        // Determine friendly reason for email
        $friendlyReason = $reason;
        if ($reason === 'user_cancel_session') {
            $friendlyReason = 'Khách hàng yêu cầu hủy';
        } elseif ($reason === 'auto_cancel_session') {
            $friendlyReason = 'Hệ thống hủy do không tìm được nhân viên';
        }

        // Send email
        $this->sendOrderEmail($booking, 'session_cancelled', [
            'reason' => $friendlyReason,
            'refund_amount' => $refundAmount,
            'payment_method' => $paymentMethod,
            'session_date' => $sessionDate,
            'session_time' => $sessionTime,
            'session_value' => $sessionValue,
        ]);
        
        $this->pushToCustomer(
            $booking,
            'Hủy buổi làm việc',
            $message
        );

        return $notification;
    }
}
