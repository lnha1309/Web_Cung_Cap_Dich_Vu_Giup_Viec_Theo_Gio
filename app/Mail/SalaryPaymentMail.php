<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalaryPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    /**
     * @param array $data keys: employee_name, employee_id, salary_amount, balance_before, balance_after, transaction_id, payment_date
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $employeeName = $this->data['employee_name'] ?? '';
        $subject = "Thông báo thanh toán lương - {$employeeName}";

        return $this->subject($subject)
            ->view('emails.salary_payment')
            ->with($this->data);
    }
}
