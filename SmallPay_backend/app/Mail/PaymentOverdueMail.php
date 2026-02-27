<?php

namespace App\Mail;

use App\Models\PaymentSchedule;
use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentOverdueMail extends Mailable
{
    use Queueable, SerializesModels;

    public PaymentSchedule $schedule;
    public User $user;
    public Order $order;
    public int $daysOverdue;

    public function __construct(PaymentSchedule $schedule, User $user, Order $order)
    {
        $this->schedule = $schedule;
        $this->user = $user;
        $this->order = $order;
        $this->daysOverdue = now()->diffInDays($schedule->due_date, false);
    }

    public function build()
    {
        return $this->subject('Échéance Dépassée - Action Requise - SmallPay')
            ->view('emails.payment_overdue')
            ->with([
                'user' => $this->user,
                'schedule' => $this->schedule,
                'order' => $this->order,
                'daysOverdue' => $this->daysOverdue,
            ]);
    }
}
