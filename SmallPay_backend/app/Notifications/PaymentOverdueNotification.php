<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\PaymentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(protected Order $order, protected PaymentSchedule $schedule)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Paiement en retard - ' . ($this->order->order_number ?? ('#' . $this->order->id)))
            ->view('emails.payment_overdue', [
                'user' => $notifiable,
                'order' => $this->order,
                'schedule' => $this->schedule,
            ]);
    }
}
