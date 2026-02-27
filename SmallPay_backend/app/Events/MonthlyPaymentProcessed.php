<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonthlyPaymentProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public User $user;
    public Order $order;

    public function __construct(Payment $payment, User $user, Order $order)
    {
        $this->payment = $payment;
        $this->user = $user;
        $this->order = $order;
    }
}
