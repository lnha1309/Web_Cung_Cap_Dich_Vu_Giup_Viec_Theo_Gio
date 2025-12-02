<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    /**
     * @param array $data keys: booking, type(created/cancelled/failed), customer_name, service_name, start_time, address, amount, reason, refund_amount, payment_method
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $bookingId = $this->data['booking']->ID_DD ?? '';
        $type = $this->data['type'] ?? 'created';

        $subjectMap = [
            'created' => "Xác nhận đặt đơn #{$bookingId}",
            'cancelled' => "Đơn #{$bookingId} đã được hủy",
            'failed' => "Thanh toán đơn #{$bookingId} không thành công",
            'session_cancelled' => "Hủy buổi làm trong đơn #{$bookingId}",
        ];

        $subject = $subjectMap[$type] ?? ("Thông báo đơn #{$bookingId}");

        return $this->subject($subject)
            ->view('emails.order_status')
            ->with($this->data);
    }
}
