<?php

namespace App\Mail;

use App\Models\PaymentSchedule;
use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public PaymentSchedule $schedule;
    public User $user;
    public Order $order;
    public int $daysRemaining;

    public function __construct(PaymentSchedule $schedule, User $user, Order $order)
    {
        $this->schedule = $schedule;
        $this->user = $user;
        $this->order = $order;
        $this->daysRemaining = now()->diffInDays($schedule->due_date, false);
    }

    public function build()
    {
        return $this->subject('Rappel d\'Échéance - Versement à Effectuer - SmallPay')
            ->view('emails.payment_due_reminder')
            ->with([
                'user' => $this->user,
                'schedule' => $this->schedule,
                'order' => $this->order,
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
