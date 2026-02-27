<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserBlockedNotification extends Notification
{
    use Queueable;

    public function __construct(protected User $user)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Compte bloqué')
            ->view('emails.user_blocked', [
                'user' => $this->user,
            ]);
    }
}
