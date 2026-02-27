<?php

namespace App\Services;

use App\Mail\KYCSubmittedMail;
use App\Mail\FirstPaymentMail;
use App\Mail\PaymentDueReminderMail;
use App\Mail\MonthlyPaymentMail;
use App\Mail\PaymentOverdueMail;
use App\Mail\AdminNotificationMail;
use App\Models\KYC;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    private string $adminEmail;
    private string $adminName = 'SmallPay Admin';

    public function __construct()
    {
        $this->adminEmail = config('app.admin_email', 'contact@godloveshop.cm');
    }

    /**
     * Envoyer un email de soumission KYC à l'utilisateur et l'admin
     */
    public function sendKYCSubmittedEmails(KYC $kyc): void
    {
        try {
            // Email à l'utilisateur
            if ($kyc->user && $kyc->user->email) {
                Mail::to($kyc->user->email)
                    ->queue(new KYCSubmittedMail($kyc, $kyc->user));
                Log::info("KYC submitted email queued for user {$kyc->user->id}");
            }

            // Email à l'admin
            $this->notifyAdminKYCSubmitted($kyc);
        } catch (\Exception $e) {
            Log::error("Error sending KYC submitted emails: {$e->getMessage()}");
        }
    }

    /**
     * Envoyer un email de premier versement à l'utilisateur et l'admin
     */
    public function sendFirstPaymentEmails(Payment $payment): void
    {
        try {
            $order = $payment->order;
            $user = $order->user;

            if (!$user || !$user->email) {
                Log::warning("No user email found for first payment");
                return;
            }

            // Email à l'utilisateur
            Mail::to($user->email)
                ->queue(new FirstPaymentMail($payment, $user, $order));
            Log::info("First payment email queued for user {$user->id}");

            // Email à l'admin
            $this->notifyAdminFirstPayment($payment, $user, $order);
        } catch (\Exception $e) {
            Log::error("Error sending first payment emails: {$e->getMessage()}");
        }
    }

    /**
     * Envoyer un rappel d'échéance (3 jours avant)
     */
    public function sendPaymentDueReminderEmails(PaymentSchedule $schedule): void
    {
        try {
            $order = $schedule->order;
            $user = $order->user;

            if (!$user || !$user->email) {
                Log::warning("No user email found for payment reminder");
                return;
            }

            // Email à l'utilisateur
            Mail::to($user->email)
                ->queue(new PaymentDueReminderMail($schedule, $user, $order));
            Log::info("Payment reminder email queued for user {$user->id}");

            // Email à l'admin
            $this->notifyAdminPaymentReminder($schedule, $user, $order);
        } catch (\Exception $e) {
            Log::error("Error sending payment reminder emails: {$e->getMessage()}");
        }
    }

    /**
     * Envoyer un email de versement mensuel
     */
    public function sendMonthlyPaymentEmails(Payment $payment): void
    {
        try {
            $order = $payment->order;
            $user = $order->user;

            if (!$user || !$user->email) {
                Log::warning("No user email found for monthly payment");
                return;
            }

            // Email à l'utilisateur
            Mail::to($user->email)
                ->queue(new MonthlyPaymentMail($payment, $user, $order));
            Log::info("Monthly payment email queued for user {$user->id}");

            // Email à l'admin
            $this->notifyAdminMonthlyPayment($payment, $user, $order);
        } catch (\Exception $e) {
            Log::error("Error sending monthly payment emails: {$e->getMessage()}");
        }
    }

    /**
     * Envoyer un email d'échéance dépassée
     */
    public function sendPaymentOverdueEmails(PaymentSchedule $schedule): void
    {
        try {
            $order = $schedule->order;
            $user = $order->user;

            if (!$user || !$user->email) {
                Log::warning("No user email found for overdue payment");
                return;
            }

            // Email à l'utilisateur
            Mail::to($user->email)
                ->queue(new PaymentOverdueMail($schedule, $user, $order));
            Log::info("Overdue payment email queued for user {$user->id}");

            // Email à l'admin
            $this->notifyAdminPaymentOverdue($schedule, $user, $order);
        } catch (\Exception $e) {
            Log::error("Error sending overdue payment emails: {$e->getMessage()}");
        }
    }

    /**
     * Notifier l'admin de la soumission d'un KYC
     */
    private function notifyAdminKYCSubmitted(KYC $kyc): void
    {
        $data = [
            'user_name' => $kyc->user->name ?? 'Unknown',
            'user_email' => $kyc->user->email ?? 'N/A',
            'user_phone' => $kyc->user->phone ?? 'N/A',
            'submission_date' => $kyc->created_at->format('d/m/Y H:i'),
            'status' => $kyc->status,
        ];

        Mail::to($this->adminEmail)
            ->queue(new AdminNotificationMail(
                'Nouveau KYC Soumis - SmallPay',
                'kyc_submitted',
                $data
            ));

        Log::info("KYC submission notification queued for admin");
    }

    /**
     * Notifier l'admin du premier versement
     */
    private function notifyAdminFirstPayment(Payment $payment, User $user, Order $order): void
    {
        $data = [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'payment_amount' => number_format($payment->amount, 2, ',', ' '),
            'payment_method' => $payment->getMethodLabel(),
            'order_id' => $order->id,
            'payment_date' => $payment->payment_date->format('d/m/Y'),
            'transaction_id' => $payment->transaction_id ?? 'N/A',
        ];

        Mail::to($this->adminEmail)
            ->queue(new AdminNotificationMail(
                'Premier Versement Confirmé - SmallPay',
                'first_payment',
                $data
            ));

        Log::info("First payment notification queued for admin");
    }

    /**
     * Notifier l'admin du rappel d'échéance
     */
    private function notifyAdminPaymentReminder(PaymentSchedule $schedule, User $user, Order $order): void
    {
        $daysRemaining = now()->diffInDays($schedule->due_date, false);

        $data = [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'installment_number' => $schedule->installment_number,
            'payment_amount' => number_format($schedule->amount, 2, ',', ' '),
            'due_date' => $schedule->due_date->format('d/m/Y'),
            'days_remaining' => $daysRemaining,
            'order_id' => $order->id,
        ];

        Mail::to($this->adminEmail)
            ->queue(new AdminNotificationMail(
                "Rappel d'Échéance (3 jours) - SmallPay",
                'payment_reminder',
                $data
            ));

        Log::info("Payment reminder notification queued for admin");
    }

    /**
     * Notifier l'admin du versement mensuel
     */
    private function notifyAdminMonthlyPayment(Payment $payment, User $user, Order $order): void
    {
        $data = [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'payment_amount' => number_format($payment->amount, 2, ',', ' '),
            'payment_method' => $payment->getMethodLabel(),
            'order_id' => $order->id,
            'installment_number' => $payment->schedule->installment_number ?? 'N/A',
            'payment_date' => $payment->payment_date->format('d/m/Y'),
            'transaction_id' => $payment->transaction_id ?? 'N/A',
        ];

        Mail::to($this->adminEmail)
            ->queue(new AdminNotificationMail(
                'Versement Mensuel Confirmé - SmallPay',
                'monthly_payment',
                $data
            ));

        Log::info("Monthly payment notification queued for admin");
    }

    /**
     * Notifier l'admin de l'échéance dépassée
     */
    private function notifyAdminPaymentOverdue(PaymentSchedule $schedule, User $user, Order $order): void
    {
        $daysOverdue = now()->diffInDays($schedule->due_date, false);

        $data = [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'installment_number' => $schedule->installment_number,
            'payment_amount' => number_format($schedule->amount, 2, ',', ' '),
            'due_date' => $schedule->due_date->format('d/m/Y'),
            'days_overdue' => $daysOverdue,
            'order_id' => $order->id,
        ];

        Mail::to($this->adminEmail)
            ->queue(new AdminNotificationMail(
                'Échéance Dépassée - Action Requise - SmallPay',
                'payment_overdue',
                $data
            ));

        Log::info("Payment overdue notification queued for admin");
    }
}
