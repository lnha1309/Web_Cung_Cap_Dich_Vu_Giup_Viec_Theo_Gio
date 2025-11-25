<?php

namespace App\Services;

use App\Models\ThongBao;
use App\Models\DonDat;
use Illuminate\Support\Facades\Log;

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
        $importantChanges = ['assigned', 'confirmed', 'rejected', 'completed', 'done', 'finding_staff'];
        
        if (isset($messages[$key])) {
            return $messages[$key];
        } elseif (in_array($newStatus, $importantChanges)) {
            return 'đã chuyển sang trạng thái: ' . $this->getStatusText($newStatus);
        }

        return null; // Don't notify for minor changes
    }
}
