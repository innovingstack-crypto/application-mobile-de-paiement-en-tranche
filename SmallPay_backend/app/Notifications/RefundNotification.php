<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RefundNotification extends Notification
{
    use Queueable;

    public function __construct(protected Order $order, protected Payment $payment)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Remboursement traité - ' . ($this->order->order_number ?? ('#' . $this->order->id)))
            ->view('emails.refund', [
                'user' => $notifiable,
                'order' => $this->order,
                'payment' => $this->payment,
            ]);
    }
}
