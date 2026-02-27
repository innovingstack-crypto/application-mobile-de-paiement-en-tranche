<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Initier un paiement
     */
    public function initiatePayment($orderId, $amount, $paymentMethod, $userId)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->user_id !== $userId) {
            throw new \Exception('Unauthorized');
        }
        
        if ($order->user->isBlocked()) {
            throw new \Exception('User account is blocked');
        }
        
        // Créer le paiement
        $payment = Payment::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'transaction_id' => $this->generateTransactionId(),
        ]);
        
        return $payment;
    }

    /**
     * Traiter un paiement réussi (webhook)
     */
    public function processPaymentSuccess($paymentId, $externalReference = null)
    {
        return DB::transaction(function () use ($paymentId, $externalReference) {
            $payment = Payment::findOrFail($paymentId);
            
            if ($payment->status === 'success') {
                throw new \Exception('Payment already processed');
            }
            
            // Marquer le paiement comme réussi
            $payment->markAsSuccess($externalReference);
            
            $order = $payment->order;
            
            // Mettre à jour l'échéancier
            $this->updatePaymentSchedule($order, $payment->amount);
            
            // Mettre à jour le statut de paiement de la commande
            $this->updateOrderPaymentStatus($order);
            
            // Envoyer une notification
            $this->notificationService->notifyPaymentSuccess($order, $payment);
            
            return $payment;
        });
    }

    /**
     * Traiter un paiement échoué
     */
    public function processPaymentFailure($paymentId, $reason = null)
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->markAsFailed($reason);
        
        $this->notificationService->notifyPaymentFailed($payment->order, $payment);
        
        return $payment;
    }

    /**
     * Mettre à jour l'échéancier après paiement
     */
    protected function updatePaymentSchedule($order, $amount)
    {
        // Récupérer l'échéance en attente la plus ancienne
        $schedule = $order->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->first();
        
        if ($schedule) {
            $schedule->addPayment($amount);
        }
    }

    /**
     * Mettre à jour le statut de paiement de la commande
     */
    protected function updateOrderPaymentStatus($order)
    {
        $totalPaid = $order->getTotalPaidAmount();
        $totalAmount = $order->total_amount;
        
        if ($totalPaid >= $totalAmount) {
            $order->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $order->update(['payment_status' => 'partial']);
        }
    }

    /**
     * Récupérer l'historique des paiements
     */
    public function getPaymentHistory($orderId, $limit = 50)
    {
        return Payment::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Générer un ID de transaction unique
     */
    protected function generateTransactionId()
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    /**
     * Obtenir le montant total payé pour une commande
     */
    public function getTotalPaidAmount($orderId)
    {
        return Payment::where('order_id', $orderId)
            ->where('status', 'success')
            ->sum('amount');
    }

    /**
     * Obtenir le montant restant à payer
     */
    public function getRemainingAmount($orderId)
    {
        $order = Order::findOrFail($orderId);
        $totalPaid = $this->getTotalPaidAmount($orderId);
        
        return $order->total_amount - $totalPaid;
    }

    /**
     * Rembouser un paiement
     */
    public function refundPayment($paymentId, $reason = null)
    {
        return DB::transaction(function () use ($paymentId, $reason) {
            $payment = Payment::findOrFail($paymentId);
            
            if ($payment->status !== 'success') {
                throw new \Exception('Can only refund successful payments');
            }
            
            // Créer une transaction de remboursement
            Transaction::create([
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'type' => 'refund',
                'amount' => $payment->amount,
                'status' => 'success',
                'description' => "Refund of {$payment->amount}: {$reason}",
            ]);
            
            $this->notificationService->notifyRefund($payment->order, $payment);
            
            return true;
        });
    }
}