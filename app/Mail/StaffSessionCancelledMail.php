<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffSessionCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    /**
     * @param array $data keys: staff_name, session_date, session_time, order_id, reason
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $orderId = $this->data['order_id'] ?? '';
        $sessionDate = $this->data['session_date'] ?? '';
        $subject = "Thông báo hủy buổi làm việc - Đơn #{$orderId} - Ngày {$sessionDate}";

        return $this->subject($subject)
            ->view('emails.staff_session_cancelled')
            ->with($this->data);
    }
}
