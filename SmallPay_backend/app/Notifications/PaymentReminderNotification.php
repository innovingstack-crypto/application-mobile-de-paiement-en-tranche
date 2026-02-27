<?php

namespace App\Notifications;

use App\Models\PaymentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(protected PaymentSchedule $schedule)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $order = $this->schedule->order;

        return (new MailMessage)
            ->subject('Rappel de paiement - échéance')
            ->view('emails.payment_reminder', [
                'user' => $notifiable,
                'schedule' => $this->schedule,
                'order' => $order,
            ]);
    }
}
