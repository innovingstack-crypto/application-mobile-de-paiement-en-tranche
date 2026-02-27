<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentSuccessNotification extends Notification
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
            ->subject('Paiement reçu - ' . ($this->order->order_number ?? ('#' . $this->order->id)))
            ->view('emails.payment_success', [
                'user' => $notifiable,
                'order' => $this->order,
                'payment' => $this->payment,
            ]);
    }
}
