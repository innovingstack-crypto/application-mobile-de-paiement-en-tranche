<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FirstPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;
    public User $user;
    public Order $order;

    public function __construct(Payment $payment, User $user, Order $order)
    {
        $this->payment = $payment;
        $this->user = $user;
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Premier Versement Confirmé - SmallPay')
            ->view('emails.first_payment')
            ->with([
                'user' => $this->user,
                'payment' => $this->payment,
                'order' => $this->order,
            ]);
    }
}
