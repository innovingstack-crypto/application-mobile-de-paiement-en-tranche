<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Order $order, protected string $newStatus)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Mise à jour de votre commande - ' . ($this->order->order_number ?? ('#' . $this->order->id)))
            ->view('emails.order_status_changed', [
                'user' => $notifiable,
                'order' => $this->order,
                'newStatus' => $this->newStatus,
            ]);
    }
}
