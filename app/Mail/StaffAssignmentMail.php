<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    /**
     * @param array $data keys: staff_name, service_name, customer_name, session_date, session_time, address, order_id, session_id
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $orderId = $this->data['order_id'] ?? '';
        $subject = "Bạn được phân công làm việc - Đơn #{$orderId}";

        return $this->subject($subject)
            ->view('emails.staff_assignment')
            ->with($this->data);
    }
}
