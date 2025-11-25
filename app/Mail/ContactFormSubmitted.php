<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): self
    {
        return $this
            ->subject('Lien he moi tu khach hang')
            ->replyTo($this->data['email'], $this->data['name'])
            ->view('emails.contact_form')
            ->with(['data' => $this->data]);
    }
}
