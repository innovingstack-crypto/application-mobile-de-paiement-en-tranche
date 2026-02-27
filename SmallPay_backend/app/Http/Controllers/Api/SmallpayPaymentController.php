<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\CampayPayment;
use App\Services\PaymentFlowService;
use App\Services\CampayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SmallpayPaymentController extends Controller
{
    private PaymentFlowService $paymentFlowService;
    private CampayService $campayService;

    public function __construct(PaymentFlowService $paymentFlowService, CampayService $campayService)
    {
        $this->paymentFlowService = $paymentFlowService;
        $this->campayService = $campayService;
    }

    /**
     * Initier le paiement du dépôt après approbation KYC
     * 
     * POST /api/payments/deposit
     * {
     *   "order_id": 1
     * }
     */
    public function initiateDeposit(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
            ]);

            $user = Auth::user();
            $order = Order::findOrFail($validated['order_id']);

            // Vérifier les conditions préalables
            $canPay = $this->paymentFlowService->canInitiateDeposit($user, $order);
            if (!$canPay['can_proceed']) {
                return response()->json([
                    'success' => false,
                    'error' => 'PAYMENT_NOT_ALLOWED',
                    'message' => $canPay['reason'],
                ], 403);
            }

            // Initier le paiement via Campay
            $response = $this->campayService->initiateSmallpayPayment($user, $order, 'deposit');

            if (!$response['success']) {
                Log::warning('deposit_payment_initiation_failed', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'error' => $response['error'],
                ]);

                return response()->json($response, 422);
            }

            // Enregistrer le paiement en base (status: pending)
            $campayPayment = CampayPayment::create([
                'reference' => $response['reference'],
                'amount' => $response['amount'],
                'currency' => $response['currency'],
                'phone' => $request->user()->phone,
                'status' => 'pending',
                'description' => $response['payment_details']['description'],
                'provider' => 'campay',
                'user_id' => $user->id,
                'order_id' => $order->id,
                'payment_type' => 'deposit',
                'meta' => [
                    'order_total' => $response['payment_details']['order_total'],
                    'remaining_after_deposit' => $response['payment_details']['remaining_after_deposit'],
                    'payment_duration' => $response['payment_details']['payment_duration'],
                    'initiated_at' => now()->toDateTimeString(),
                ],
            ]);

            Log::info('deposit_payment_initiated', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'reference' => $campayPayment->reference,
                'amount' => $campayPayment->amount,
            ]);

            return response()->json([
                'success' => true,
                'reference' => $campayPayment->reference,
                'amount' => $campayPayment->amount,
                'currency' => $campayPayment->currency,
                'message' => 'Veuillez approuver le paiement sur votre téléphone',
                'redirect_to' => 'campay_payment_screen',
                'redirect_url' => config('app.url') . "/payment/processing/{$campayPayment->reference}",
            ], 200);

        } catch (\Exception $e) {
            Log::error('deposit_payment_error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'PAYMENT_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initier un paiement mensuel
     * 
     * POST /api/payments/monthly
     * {
     *   "order_id": 1
     * }
     */
    public function initiateMonthlyPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
            ]);

            $user = Auth::user();
            $order = Order::findOrFail($validated['order_id']);

            // Vérifier les conditions préalables
            $canPay = $this->paymentFlowService->canInitiateMonthlyPayment($user, $order);
            if (!$canPay['can_proceed']) {
                return response()->json([
                    'success' => false,
                    'error' => 'PAYMENT_NOT_ALLOWED',
                    'message' => $canPay['reason'],
                ], 403);
            }

            // Initier le paiement via Campay
            $response = $this->campayService->initiateSmallpayPayment($user, $order, 'installment');

            if (!$response['success']) {
                return response()->json($response, 422);
            }

            // Enregistrer le paiement
            $scheduleId = $response['payment_details']['schedule_id'] ?? null;
            $campayPayment = CampayPayment::create([
                'reference' => $response['reference'],
                'amount' => $response['amount'],
                'currency' => $response['currency'],
                'phone' => $user->phone,
                'status' => 'pending',
                'description' => $response['payment_details']['description'],
                'provider' => 'campay',
                'user_id' => $user->id,
                'order_id' => $order->id,
                'payment_schedule_id' => $scheduleId,
                'payment_type' => 'installment',
                'meta' => [
                    'installment_number' => $response['payment_details']['installment_number'],
                    'total_installments' => $response['payment_details']['total_installments'],
                    'initiated_at' => now()->toDateTimeString(),
                ],
            ]);

            Log::info('monthly_payment_initiated', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'reference' => $campayPayment->reference,
                'amount' => $campayPayment->amount,
            ]);

            return response()->json([
                'success' => true,
                'reference' => $campayPayment->reference,
                'amount' => $campayPayment->amount,
                'currency' => $campayPayment->currency,
                'installment_number' => $response['payment_details']['installment_number'],
                'total_installments' => $response['payment_details']['total_installments'],
                'message' => 'Veuillez approuver le paiement sur votre téléphone',
                'redirect_to' => 'campay_payment_screen',
                'redirect_url' => config('app.url') . "/payment/processing/{$campayPayment->reference}",
            ], 200);

        } catch (\Exception $e) {
            Log::error('monthly_payment_error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'PAYMENT_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer le statut d'un paiement
     * 
     * GET /api/payments/{reference}/status
     */
    public function getPaymentStatus(string $reference)
    {
        try {
            $campayPayment = CampayPayment::where('reference', $reference)
                ->with(['user', 'order', 'schedule'])
                ->firstOrFail();

            // Vérifier que l'utilisateur a accès à ce paiement
            if ($campayPayment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                ], 403);
            }

            // Si le paiement est déjà finalisé, retourner l'état connu
            if (in_array($campayPayment->status, ['failed', 'success'])) {
                $response = [
                    'success' => true,
                    'reference' => $campayPayment->reference,
                    'status' => $campayPayment->status,
                    'amount' => $campayPayment->amount,
                    'payment_type' => $campayPayment->payment_type,
                ];

                if ($campayPayment->status === 'success') {
                    $response['redirect_url'] = config('app.url') . "/payment/success?reference={$reference}";
                    $response['message'] = 'Paiement réussi';
                } else {
                    $response['redirect_url'] = config('app.url') . '/payment/failed';
                    $response['error_reason'] = $campayPayment->getErrorReason();
                    $response['message'] = 'Le paiement a échoué';
                }

                return response()->json($response);
            }

            // Vérifier le statut auprès de Campay
            $campayStatus = $this->campayService->getTransactionStatus($reference);

            if (!($campayStatus['success'] ?? false)) {
                Log::warning('campay_status_check_failed', [
                    'reference' => $reference,
                    'error' => $campayStatus['error'] ?? $campayStatus,
                ]);

                return response()->json([
                    'success' => true,
                    'reference' => $reference,
                    'status' => 'pending',
                    'message' => 'Vérification en cours...',
                ]);
            }

            $apiStatus = strtolower($campayStatus['status'] ?? 'unknown');

            if ($apiStatus === 'success') {
                // Finaliser le paiement selon son type
                if ($campayPayment->isDeposit()) {
                    $this->paymentFlowService->finalizeDepositPayment($campayPayment);
                } else {
                    $this->paymentFlowService->finalizeMonthlyPayment($campayPayment);
                }

                $campayPayment->update(['status' => 'success']);

                return response()->json([
                    'success' => true,
                    'reference' => $reference,
                    'status' => 'success',
                    'message' => 'Paiement réussi',
                    'redirect_url' => config('app.url') . "/payment/success?reference={$reference}",
                ]);
            }

            if ($apiStatus === 'failed') {
                $meta = $campayPayment->meta ?? [];
                $meta['error_code'] = $campayStatus['error_code'] ?? null;
                $meta['error_reason'] = $campayStatus['error_message'] ?? CampayService::getErrorReason($campayStatus['error_code'] ?? '');

                $campayPayment->update([
                    'status' => 'failed',
                    'meta' => $meta,
                ]);

                return response()->json([
                    'success' => true,
                    'reference' => $reference,
                    'status' => 'failed',
                    'error_code' => $meta['error_code'],
                    'error_reason' => $meta['error_reason'],
                    'redirect_url' => config('app.url') . '/payment/failed',
                    'message' => 'Le paiement a échoué',
                ]);
            }

            // Status: pending
            return response()->json([
                'success' => true,
                'reference' => $reference,
                'status' => 'pending',
                'message' => 'Paiement en cours de traitement',
            ]);

        } catch (\Exception $e) {
            Log::error('payment_status_error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'STATUS_CHECK_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des paiements d'une commande
     * 
     * GET /api/orders/{orderId}/payments
     */
    public function getOrderPayments($orderId)
    {
        try {
            $user = Auth::user();
            $order = Order::findOrFail($orderId);

            // Vérifier que l'utilisateur a accès à cette commande
            if ($order->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                ], 403);
            }

            $payments = $order->payments()
                ->with('schedule')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'method' => $payment->method,
                    'transaction_id' => $payment->transaction_id,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date,
                    'installment_number' => $payment->schedule?->installment_number,
                ]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'payments' => $payments,
                'summary' => $this->paymentFlowService->getPaymentSummary($order),
            ]);

        } catch (\Exception $e) {
            Log::error('get_order_payments_error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer le planning de paiement d'une commande
     * 
     * GET /api/orders/{orderId}/schedule
     */
    public function getPaymentSchedule($orderId)
    {
        try {
            $user = Auth::user();
            $order = Order::findOrFail($orderId);

            // Vérifier que l'utilisateur a accès à cette commande
            if ($order->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'UNAUTHORIZED',
                ], 403);
            }

            $schedules = $order->schedules()
                ->orderBy('installment_number', 'asc')
                ->get()
                ->map(fn($schedule) => [
                    'id' => $schedule->id,
                    'installment_number' => $schedule->installment_number,
                    'amount' => (float) $schedule->amount,
                    'due_date' => $schedule->due_date,
                    'status' => $schedule->status,
                    'is_overdue' => $schedule->isOverdue(),
                    'days_until_due' => $schedule->getDaysUntilDue(),
                ]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'payment_duration' => $order->payment_duration,
                'total_amount' => (float) $order->total_amount,
                'remaining_amount' => (float) $order->remaining_amount,
                'schedules' => $schedules,
                'summary' => $this->paymentFlowService->getPaymentSummary($order),
            ]);

        } catch (\Exception $e) {
            Log::error('get_payment_schedule_error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
