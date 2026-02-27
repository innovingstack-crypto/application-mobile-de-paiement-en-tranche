<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public array $lines;

    public function __construct(string $title, array $lines)
    {
        $this->title = $title;
        $this->lines = $lines;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->view('emails.admin_alert')
            ->with([
                'title' => $this->title,
                'lines' => $this->lines,
            ]);
    }
}


