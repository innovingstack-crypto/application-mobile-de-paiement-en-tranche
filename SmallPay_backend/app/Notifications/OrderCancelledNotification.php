<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(protected Order $order)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Commande annulée - ' . ($this->order->order_number ?? ('#' . $this->order->id)))
            ->view('emails.order_cancelled', [
                'user' => $notifiable,
                'order' => $this->order,
            ]);
    }
}
