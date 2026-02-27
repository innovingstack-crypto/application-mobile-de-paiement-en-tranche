<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\KYC;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\CampayPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentFlowService
{
    /**
     * Vérifier si un utilisateur peut initier un paiement de dépôt
     */
    public function canInitiateDeposit(User $user, Order $order): array
    {
        $can = true;
        $reason = null;

        // Vérifier si l'utilisateur existe et est actif
        if (!$user || $user->status !== 'active') {
            $can = false;
            $reason = 'Utilisateur inactif ou suspendu';
        }

        // Vérifier si la commande existe et appartient à l'utilisateur
        if ($order->user_id !== $user->id) {
            $can = false;
            $reason = 'Cette commande ne vous appartient pas';
        }

        // Vérifier si le KYC est approuvé (si requis)
        if ($order->is_kyc_required) {
            $kyc = $user->kyc;
            if (!$kyc || $kyc->status !== 'approved') {
                $can = false;
                $reason = 'Votre KYC doit être approuvé pour effectuer le paiement';
            }
        }

        // Vérifier si le dépôt n'a pas déjà été payé
        if ($order->payment_status !== 'pending') {
            $can = false;
            $reason = 'Le dépôt de cette commande a déjà été payé';
        }

        // Vérifier si la commande n'est pas annulée
        if ($order->status === 'cancelled') {
            $can = false;
            $reason = 'Cette commande a été annulée';
        }

        Log::info('canInitiateDeposit check', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'can' => $can,
            'reason' => $reason,
        ]);

        return [
            'can_proceed' => $can,
            'reason' => $reason,
        ];
    }

    /**
     * Vérifier si un utilisateur peut initier un paiement mensuel
     */
    public function canInitiateMonthlyPayment(User $user, Order $order): array
    {
        $can = true;
        $reason = null;

        // Vérifier si l'utilisateur existe
        if (!$user || $user->status !== 'active') {
            $can = false;
            $reason = 'Utilisateur inactif ou suspendu';
        }

        // Vérifier si la commande appartient à l'utilisateur
        if ($order->user_id !== $user->id) {
            $can = false;
            $reason = 'Cette commande ne vous appartient pas';
        }

        // Vérifier si le dépôt a été payé
        if ($order->payment_status === 'pending') {
            $can = false;
            $reason = 'Le dépôt n\'a pas été payé. Veuillez d\'abord payer l\'acompte';
        }

        // Vérifier si la commande est active
        if ($order->status !== 'active') {
            $can = false;
            $reason = 'Cette commande n\'est pas active';
        }

        // Vérifier s'il y a un paiement dû
        $dueSchedules = $order->schedules()
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_date', '<=', now())
            ->count();

        if ($dueSchedules === 0) {
            $can = false;
            $reason = 'Aucun paiement ne est actuellement dû';
        }

        Log::info('canInitiateMonthlyPayment check', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'can' => $can,
            'reason' => $reason,
        ]);

        return [
            'can_proceed' => $can,
            'reason' => $reason,
        ];
    }

    /**
     * Préparer les détails du paiement de dépôt
     */
    public function prepareDepositPayment(User $user, Order $order): array
    {
        $depositAmount = $order->deposit_amount;
        $description = "Acompte de commande - Montant total: {$order->total_amount} XAF (60%)";
        
        // Extraire le numéro de téléphone (format: +237XXXXXXXXX ou 6XXXXXXXXX)
        $phone = $this->formatPhoneNumber($user->phone);

        return [
            'amount' => $depositAmount,
            'phone' => $phone,
            'currency' => config('campay.currency', 'XAF'),
            'description' => $description,
            'type' => 'deposit',
            'order_id' => $order->id,
            'user_id' => $user->id,
            'order_total' => $order->total_amount,
            'remaining_after_deposit' => $order->remaining_amount,
            'payment_duration' => $order->payment_duration,
        ];
    }

    /**
     * Préparer les détails d'un paiement mensuel
     */
    public function prepareMonthlyPayment(User $user, Order $order): array
    {
        // Récupérer l'échéance la plus proche qui est due
        $schedule = $order->schedules()
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_date', '<=', now())
            ->orderBy('installment_number', 'asc')
            ->first();

        if (!$schedule) {
            return [
                'error' => 'Aucun paiement dû trouvé',
            ];
        }

        $phone = $this->formatPhoneNumber($user->phone);
        $monthNumber = $schedule->installment_number;

        return [
            'amount' => $schedule->amount,
            'phone' => $phone,
            'currency' => config('campay.currency', 'XAF'),
            'description' => "Mensualité {$monthNumber}/{$order->payment_duration} - Commande #{$order->id}",
            'type' => 'installment',
            'order_id' => $order->id,
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'installment_number' => $monthNumber,
            'total_installments' => $order->payment_duration,
        ];
    }

    /**
     * Générer le planning de paiement après paiement du dépôt
     * 
     * Crée N échéances de paiement mensuel égal
     */
    public function generatePaymentSchedule(Order $order, Carbon $startDate = null): Collection
    {
        $startDate = $startDate ?? now();
        $schedules = new Collection();
        
        // Montant à payer chaque mois
        $monthlyAmount = $order->remaining_amount / $order->payment_duration;
        
        // Arrondir à 2 décimales
        $monthlyAmount = round($monthlyAmount, 2);
        
        // Différence (pour la dernière mensualité)
        $difference = $order->remaining_amount - ($monthlyAmount * ($order->payment_duration - 1));

        try {
            DB::transaction(function () use ($order, $startDate, $monthlyAmount, $difference, &$schedules) {
                for ($i = 1; $i <= $order->payment_duration; $i++) {
                    $dueDate = $startDate->copy()->addMonths($i - 1);
                    
                    // La dernière mensualité peut être différente (arrondissement)
                    $amount = ($i === $order->payment_duration) ? $difference : $monthlyAmount;
                    
                    $schedule = PaymentSchedule::create([
                        'order_id' => $order->id,
                        'due_date' => $dueDate->toDateString(),
                        'amount' => $amount,
                        'installment_number' => $i,
                        'status' => 'pending',
                    ]);
                    
                    $schedules->push($schedule);
                    
                    Log::info('payment_schedule_created', [
                        'order_id' => $order->id,
                        'installment' => $i,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('failed_to_generate_payment_schedule', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $schedules;
    }

    /**
     * Finaliser un paiement de dépôt
     */
    public function finalizeDepositPayment(CampayPayment $campayPayment): void
    {
        $user = $campayPayment->user;
        $order = $campayPayment->order;

        if (!$user || !$order) {
            Log::warning('finalizeDepositPayment: missing user or order', [
                'payment_id' => $campayPayment->id,
            ]);
            return;
        }

        DB::transaction(function () use ($campayPayment, $order, $user) {
            // Mettre à jour la commande
            $order->update([
                'payment_status' => 'completed',
                'payment_method' => 'campay',
                'deposit_reference' => $campayPayment->reference,
                'deposit_paid_at' => now(),
                'status' => 'active',
                'kyc_verified_at' => now(),
                'verified_by' => auth()->id() ?? null,
            ]);

            // Générer le planning de paiement
            $this->generatePaymentSchedule($order, now()->addMonth());

            // Créer un enregistrement Payment pour le dépôt
            Payment::create([
                'order_id' => $order->id,
                'schedule_id' => null,
                'method' => 'campay',
                'transaction_id' => $campayPayment->reference,
                'amount' => $campayPayment->amount,
                'status' => 'completed',
                'payment_date' => now()->toDateString(),
            ]);

            Log::info('deposit_payment_finalized', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'amount' => $campayPayment->amount,
                'reference' => $campayPayment->reference,
            ]);
        });
    }

    /**
     * Finaliser un paiement mensuel
     */
    public function finalizeMonthlyPayment(CampayPayment $campayPayment): void
    {
        $schedule = $campayPayment->schedule;
        $order = $campayPayment->order;
        $user = $campayPayment->user;

        if (!$user || !$order || !$schedule) {
            Log::warning('finalizeMonthlyPayment: missing entities', [
                'payment_id' => $campayPayment->id,
            ]);
            return;
        }

        DB::transaction(function () use ($campayPayment, $schedule, $order) {
            // Marquer l'échéance comme payée
            $schedule->update(['status' => 'paid']);

            // Créer l'enregistrement Payment
            Payment::create([
                'order_id' => $order->id,
                'schedule_id' => $schedule->id,
                'method' => 'campay',
                'transaction_id' => $campayPayment->reference,
                'amount' => $campayPayment->amount,
                'status' => 'completed',
                'payment_date' => now()->toDateString(),
            ]);

            // Vérifier si toutes les échéances sont payées
            $unpaidSchedules = $order->schedules()
                ->whereNotIn('status', ['paid'])
                ->count();

            if ($unpaidSchedules === 0) {
                $order->update(['status' => 'completed', 'payment_status' => 'completed']);
                Log::info('order_fully_paid', ['order_id' => $order->id]);
            }

            Log::info('monthly_payment_finalized', [
                'order_id' => $order->id,
                'schedule_id' => $schedule->id,
                'installment' => $schedule->installment_number,
                'amount' => $campayPayment->amount,
            ]);
        });
    }

    /**
     * Formater le numéro de téléphone au format Campay
     * Campay accepte: +237XXXXXXXXX ou 6XXXXXXXXX (pour Cameroun)
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Supprimer les espaces et tirets
        $phone = preg_replace('/[\s\-()]+/', '', $phone);

        // Si commence par +237, garder tel quel
        if (str_starts_with($phone, '+237')) {
            return $phone;
        }

        // Si commence par 237, ajouter le +
        if (str_starts_with($phone, '237')) {
            return '+' . $phone;
        }

        // Si commence par 6 ou 2 (Cameroun), ajouter le +237
        if (str_starts_with($phone, '6') || str_starts_with($phone, '2')) {
            return '+237' . $phone;
        }

        // Sinon, retourner tel quel
        return $phone;
    }

    /**
     * Marquer les échéances en retard
     * À appeler via cron job
     */
    public function markOverdueSchedules(): int
    {
        $updated = PaymentSchedule::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        Log::info('marked_schedules_overdue', ['count' => $updated]);

        return $updated;
    }

    /**
     * Obtenir le résumé du paiement pour une commande
     */
    public function getPaymentSummary(Order $order): array
    {
        $totalPaid = $order->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $pendingSchedules = $order->schedules()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();

        $overdueSchedules = $order->schedules()
            ->where('status', 'overdue')
            ->get();

        return [
            'order_id' => $order->id,
            'total_amount' => (float) $order->total_amount,
            'deposit_amount' => (float) $order->deposit_amount,
            'remaining_amount' => (float) $order->remaining_amount,
            'total_paid' => (float) $totalPaid,
            'balance_due' => (float) ($order->remaining_amount - $totalPaid),
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'deposit_paid_at' => $order->deposit_paid_at,
            'payment_duration' => $order->payment_duration,
            'pending_schedules' => $pendingSchedules->count(),
            'next_due_date' => $pendingSchedules->first()?->due_date,
            'next_due_amount' => $pendingSchedules->first()?->amount,
            'overdue_amount' => $overdueSchedules->sum('amount'),
            'has_overdue' => $overdueSchedules->count() > 0,
        ];
    }
}
