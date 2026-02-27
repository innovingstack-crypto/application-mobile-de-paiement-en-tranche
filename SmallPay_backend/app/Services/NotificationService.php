<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\User;
use App\Models\Payment;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\PaymentReminderNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentOverdueNotification;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\RefundNotification;
use App\Notifications\UserBlockedNotification;

class NotificationService
{
    /**
     * Notifier la création d'une commande
     */
    public function notifyOrderCreated(Order $order)
    {
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'order_created',
            'title' => 'Commande créée',
            'message' => "Votre commande {$order->order_number} a été créée. Acompte : " . number_format((float) ($order->down_payment_amount ?? 0), 2) . " XAF",
            'related_order_id' => $order->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new OrderCreatedNotification($order));
        }
    }

    /**
     * Notifier le changement de statut de commande
     */
    public function notifyOrderStatusChanged(Order $order, $newStatus)
    {
        $messages = [
            'confirmed' => 'Votre commande a été confirmée',
            'shipped' => 'Votre commande a été expédiée',
            'delivered' => 'Votre commande a été livrée',
            'cancelled' => 'Votre commande a été annulée',
        ];
        
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'order_status_changed',
            'title' => 'Statut de commande mis à jour',
            'message' => $messages[$newStatus] ?? "Le statut de votre commande a changé : {$newStatus}",
            'related_order_id' => $order->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new OrderStatusChangedNotification($order, (string) $newStatus));
        }
    }

    /**
     * Notifier un paiement réussi
     */
    public function notifyPaymentSuccess(Order $order, Payment $payment)
    {
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'payment_success',
            'title' => 'Paiement reçu',
            'message' => "Paiement de " . number_format((float) ($payment->amount ?? 0), 2) . " XAF reçu pour la commande {$order->order_number}",
            'related_order_id' => $order->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new PaymentSuccessNotification($order, $payment));
        }
    }

    /**
     * Notifier un paiement échoué
     */
    public function notifyPaymentFailed(Order $order, Payment $payment)
    {
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'payment_failed',
            'title' => 'Paiement échoué',
            'message' => "Le paiement de " . number_format((float) ($payment->amount ?? 0), 2) . " XAF a échoué. Veuillez réessayer.",
            'related_order_id' => $order->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new PaymentFailedNotification($order, $payment));
        }
    }

    /**
     * Notifier un paiement en retard
     */
    public function notifyPaymentOverdue(Order $order, PaymentSchedule $schedule)
    {
        $scheduleAmount = $schedule->amount ?? $schedule->due_amount ?? 0;

        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'payment_overdue',
            'title' => 'Paiement en retard',
            'message' => "L'échéance N°{$schedule->installment_number} de " . number_format((float) $scheduleAmount, 2) . " XAF est en retard. Veuillez payer dès que possible.",
            'related_order_id' => $order->id,
            'related_schedule_id' => $schedule->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new PaymentOverdueNotification($order, $schedule));
        }
    }

    /**
     * Notifier l'annulation d'une commande
     */
    public function notifyOrderCancelled(Order $order)
    {
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'order_cancelled',
            'title' => 'Commande annulée',
            'message' => "Votre commande {$order->order_number} a été annulée.",
            'related_order_id' => $order->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new OrderCancelledNotification($order));
        }
    }

    /**
     * Notifier un remboursement
     */
    public function notifyRefund(Order $order, Payment $payment)
    {
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'refund',
            'title' => 'Remboursement effectué',
            'message' => "Un remboursement de " . number_format((float) ($payment->amount ?? 0), 2) . " XAF a été effectué pour la commande {$order->order_number}",
            'related_order_id' => $order->id,
        ]);

        $user = $order->user;
        if ($user && !empty($user->email)) {
            $user->notify(new RefundNotification($order, $payment));
        }
    }

    /**
     * Notifier le blocage d'un utilisateur
     */
    public function notifyUserBlocked(User $user)
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'account_blocked',
            'title' => 'Compte bloqué',
            'message' => "Votre compte a été bloqué suite à plusieurs paiements en retard. Veuillez contacter le support.",
        ]);

        if (!empty($user->email)) {
            $user->notify(new UserBlockedNotification($user));
        }
    }

    /**
     * Envoyer une notification de rappel de paiement
     */
    public function sendPaymentReminder(PaymentSchedule $schedule)
    {
        $daysUntilDue = $schedule->getDaysUntilDue();
        
        if ($daysUntilDue <= 3 && $daysUntilDue > 0) {
            $scheduleAmount = $schedule->amount ?? $schedule->due_amount ?? 0;

            Notification::create([
                'user_id' => $schedule->order->user_id,
                'type' => 'payment_reminder',
                'title' => 'Rappel de paiement',
                'message' => "L'échéance N°{$schedule->installment_number} de " . number_format((float) $scheduleAmount, 2) . " XAF est due dans {$daysUntilDue} jour(s).",
                'related_order_id' => $schedule->order_id,
                'related_schedule_id' => $schedule->id,
            ]);

            $user = $schedule->order?->user;
            if ($user && !empty($user->email)) {
                $user->notify(new PaymentReminderNotification($schedule));
            }
        }
    }

    /**
     * Récupérer les notifications d'un utilisateur
     */
    public function getUserNotifications($userId, $limit = 50)
    {
        return Notification::byUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupérer le nombre de notifications non lues
     */
    public function getUnreadCount($userId)
    {
        return Notification::byUser($userId)->unread()->count();
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        $notification->markAsRead();
        return $notification;
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead($userId)
    {
        Notification::byUser($userId)->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}