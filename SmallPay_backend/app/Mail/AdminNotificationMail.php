<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subject;
    public string $type;
    public array $data;

    public function __construct(string $subject, string $type, array $data)
    {
        $this->subject = $subject;
        $this->type = $type;
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.admin_notification')
            ->with([
                'type' => $this->type,
                'data' => $this->data,
            ]);
    }
}
