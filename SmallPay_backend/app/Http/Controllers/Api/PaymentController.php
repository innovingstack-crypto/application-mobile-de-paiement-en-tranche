<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initier un paiement
     * POST /api/payments/initiate
     */
    public function initiate(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|string|in:card,bank_transfer,mobile_money,wallet',
            ]);
            
            $payment = $this->paymentService->initiatePayment(
                $request->order_id,
                $request->amount,
                $request->payment_method,
                auth('api')->id()
            );
            
            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment initiated successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Webhook de paiement (depuis la passerelle de paiement)
     * POST /api/payments/webhook
     */
    public function webhook(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|integer',
                'status' => 'required|string|in:success,failed',
                'external_reference' => 'nullable|string',
            ]);
            
            if ($request->status === 'success') {
                $payment = $this->paymentService->processPaymentSuccess(
                    $request->payment_id,
                    $request->external_reference
                );
            } else {
                $payment = $this->paymentService->processPaymentFailure(
                    $request->payment_id,
                    $request->get('reason')
                );
            }
            
            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment processed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Récupérer l'historique des paiements
     * GET /api/orders/{orderId}/payments
     */
    public function history($orderId)
    {
        try {
            $payments = $this->paymentService->getPaymentHistory($orderId);
            
            return response()->json([
                'success' => true,
                'data' => $payments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}