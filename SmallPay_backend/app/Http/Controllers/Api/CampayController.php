<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\CampayService;
use Illuminate\Http\JsonResponse;
use App\Models\CampayPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampayController extends Controller
{
    public function __construct(private CampayService $campay)
    {
    }

    public function initiate(Request $request): JsonResponse
    {
        Log::info('Campay initiate request', ['payload' => $request->all()]);

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:100',
                'phone' => 'required|string',
                'description' => 'nullable|string',
                'currency' => 'nullable|string',
                'sell_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|numeric|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'VALIDATION_ERROR',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Campay initiate validation exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'VALIDATION_EXCEPTION', 'message' => $e->getMessage()], 500);
        }

        // Convertir les quantities en entiers
        foreach ($validated['items'] as &$item) {
            $item['quantity'] = (int) $item['quantity'];
        }
        unset($item);

        $reference = (string) Str::uuid();
        Log::info('Generated reference', ['reference' => $reference]);

        // Create a local payment record isolated for Campay
        try {
            $payment = CampayPayment::create([
                'reference' => $reference,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? config('campay.currency', 'XAF'),
                'phone' => $validated['phone'],
                'status' => 'pending',
                'description' => $validated['description'] ?? 'Commande',
                'provider' => 'campay',
                'meta' => [
                    'sell_id' => $validated['sell_id'],
                    'items' => $validated['items'],
                    'processed' => false,
                    'status_check_count' => 0,
                ],
            ]);
            Log::info('Payment created', ['id' => $payment->id, 'reference' => $payment->reference]);
        } catch (\Throwable $e) {
            Log::error('Failed to create payment', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to create payment record', 'message' => $e->getMessage()], 500);
        }

        // Appel à Campay
        try {
            $resp = $this->campay->initiateCollect([
                'amount' => $payment->amount,
                'from' => $payment->phone,
                'currency' => $payment->currency,
                'description' => $payment->description,
                'reference' => $payment->reference,
            ]);
        } catch (\Throwable $e) {
            $meta = $payment->meta ?? [];
            $meta['campay_error'] = [
                'error' => 'EXCEPTION',
                'message' => $e->getMessage(),
            ];
            $payment->status = 'failed';
            $payment->meta = $meta;
            $payment->save();

            Log::error('Campay initiate exception', ['message' => $e->getMessage(), 'reference' => $payment->reference]);
            return response()->json(['success' => false, 'error' => 'CONNECTION_ERROR', 'message' => $e->getMessage()], 502);
        }

        if (!($resp['success'] ?? false)) {
            // Enregistre l'erreur complète dans la meta pour debug
            $meta = $payment->meta ?? [];
            $meta['campay_error'] = $resp;
            if (!empty($resp['error_code'])) {
                $meta['error_code'] = $resp['error_code'];
                $meta['error_reason'] = $resp['message'] ?? $resp['error'] ?? CampayService::getErrorReason($resp['error_code']);
            }
            $payment->status = 'failed';
            $payment->meta = $meta;
            $payment->save();

            Log::error('Campay initiate failed', ['error' => $resp['error'] ?? $resp, 'reference' => $payment->reference]);
            return response()->json([
                'success' => false,
                'error' => $resp['error'] ?? 'COLLECT_FAILED',
                'error_code' => $resp['error_code'] ?? null,
                'message' => $resp['message'] ?? null,
            ], 422);
        }

        Log::info('Campay payment initiated', ['reference' => $payment->reference, 'amount' => $payment->amount]);

        return response()->json([
            'success' => true,
            'reference' => $payment->reference,
            'message' => 'Veuillez approuver la demande de paiement sur votre téléphone.',
        ], 200);
    }

    public function status(string $reference): JsonResponse
    {
        $payment = CampayPayment::where('reference', $reference)->first();
        if (!$payment) {
            Log::warning('Campay status: payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'error' => 'Payment not found'], 404);
        }

        // Si déjà finalisé, retourner l'état connu
        if (in_array($payment->status, ['failed', 'success'])) {
            $response = [
                'success' => true,
                'status' => $payment->status,
            ];

            if ($payment->status === 'failed' && !empty($payment->meta)) {
                $meta = $payment->meta;
                if (!empty($meta['error_code'])) {
                    $response['error_code'] = $meta['error_code'];
                    $response['error_reason'] = $meta['error_reason'] ?? CampayService::getErrorReason($meta['error_code']);
                    $response['is_insufficient_balance'] = ($meta['error_code'] === 'ER301');
                    $response['redirect_url'] = config('app.url') . '/payment/failed';
                }
            }

            return response()->json($response);
        }

        // Limiter le nombre de polls pour éviter boucle infinie côté serveur
        $meta = $payment->meta ?? [];
        $attempts = $meta['status_check_count'] ?? 0;
        $maxAttempts = 6;
        if ($attempts >= $maxAttempts) {
            Log::warning('Campay status: max status checks reached', ['reference' => $reference, 'attempts' => $attempts]);
            return response()->json([
                'success' => false,
                'error' => 'MAX_STATUS_CHECKS_REACHED',
                'message' => 'Trop de vérifications, réessayez plus tard.'
            ], 429);
        }

        $meta['status_check_count'] = $attempts + 1;
        $payment->meta = $meta;
        $payment->save();

        $resp = $this->campay->getTransactionStatus($reference);
        if (!($resp['success'] ?? false)) {
            Log::warning('Campay status API error', ['reference' => $reference, 'error' => $resp['error'] ?? $resp]);
            if (($resp['error'] ?? '') === 'NOT_FOUND') {
                $meta = $payment->meta ?? [];
                $meta['error_code'] = $meta['error_code'] ?? 'NOT_FOUND';
                $meta['error_reason'] = $resp['message'] ?? 'Transaction not found on provider';
                $payment->status = 'failed';
                $payment->meta = $meta;
                $payment->save();

                return response()->json([
                    'success' => true,
                    'status' => 'failed',
                    'error_code' => $meta['error_code'],
                    'error_reason' => $meta['error_reason'],
                    'redirect_url' => config('app.url') . '/payment/failed',
                ], 200);
            }

            return response()->json(['success' => false, 'error' => $resp['error'] ?? 'STATUS_FAILED'], 502);
        }

        $apiStatus = strtolower($resp['status'] ?? 'unknown');
        $errorCode = $resp['error_code'] ?? null;
        $errorMsg = $resp['error_message'] ?? null;

        if ($apiStatus === 'failed') {
            $meta = $payment->meta ?? [];
            if ($errorCode) {
                $meta['error_code'] = $errorCode;
                $meta['error_reason'] = $errorMsg ?? CampayService::getErrorReason($errorCode);
            } else {
                $raw = $resp['raw'] ?? [];
                $meta['error_code'] = $meta['error_code'] ?? ($raw['code'] ?? null);
                $meta['error_reason'] = $meta['error_reason'] ?? ($raw['message'] ?? null);
            }

            $payment->status = 'failed';
            $payment->meta = $meta;
            $payment->save();

            $isInsufficient = ($meta['error_code'] ?? '') === 'ER301';

            return response()->json([
                'success' => true,
                'status' => 'failed',
                'error_code' => $meta['error_code'] ?? null,
                'error_reason' => $meta['error_reason'] ?? null,
                'is_insufficient_balance' => $isInsufficient,
                'redirect_url' => config('app.url') . '/payment/failed'
            ], 200);
        }

        if ($apiStatus === 'success') {
            if ($payment->status !== 'success') {
                $this->finalizeOrder($payment);
            }
            $payment->status = 'success';
            $payment->save();

            return response()->json([
                'success' => true,
                'status' => 'success'
            ], 200);
        }

        // pending / unknown
        return response()->json([
            'success' => true,
            'status' => $apiStatus
        ], 200);
    }

    // Callback endpoint for CamPay webhook notifications
    public function callback(Request $request): JsonResponse
    {
        Log::info('CamPay webhook received', ['payload' => $request->all()]);

        $reference = $request->input('external_reference') ?? $request->input('reference');
        if (!$reference) {
            Log::warning('CamPay webhook: missing reference');
            return response()->json(['success' => false, 'error' => 'Missing reference'], 400);
        }

        $payment = CampayPayment::where('reference', $reference)->first();
        if (!$payment) {
            Log::warning('CamPay webhook: payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'error' => 'Payment not found'], 404);
        }

        $signature = $request->header('X-Campay-Signature') ?? $request->input('signature');
        if ($signature && !$this->campay->verifyWebhookSignature($request->all(), $signature)) {
            Log::warning('CamPay webhook: invalid signature', ['reference' => $reference]);
            return response()->json(['success' => false, 'error' => 'Invalid signature'], 403);
        }

        $status = strtolower($request->input('status', ''));
        $operator = $request->input('operator', 'unknown');
        $code = $request->input('code', '');

        $meta = $payment->meta ?? [];
        $meta['webhook_data'] = [
            'operator' => $operator,
            'code' => $code,
            'operator_reference' => $request->input('operator_reference'),
            'received_at' => now()->toDateTimeString(),
        ];

        if ($status === 'successful' || $status === 'success') {
            if ($payment->status !== 'success') {
                $this->finalizeOrder($payment);
            }
            $payment->status = 'success';
            $payment->meta = $meta;
            $payment->save();
            Log::info('CamPay payment successful', ['reference' => $reference, 'amount' => $payment->amount]);
        } elseif ($status === 'failed') {
            $errorReason = CampayService::getErrorReason($code);
            $meta['error_code'] = $code;
            $meta['error_reason'] = $errorReason;

            $payment->status = 'failed';
            $payment->meta = $meta;
            $payment->save();
            Log::warning('CamPay payment failed', [
                'reference' => $reference,
                'code' => $code,
                'reason' => $errorReason,
                'operator' => $operator
            ]);
        } else {
            // stocker webhook mais ne changer le status si inconnu
            $payment->meta = $meta;
            $payment->save();
        }

        return response()->json(['success' => true], 200);
    }

    private function finalizeOrder(CampayPayment $payment): void
    {
        $meta = $payment->meta ?? [];
        if (!empty($meta['processed'])) {
            return;
        }

        $sellId = $meta['sell_id'] ?? null;
        $items = $meta['items'] ?? [];
        if (!$sellId || empty($items)) {
            Log::warning('Campay finalizeOrder missing sell_id/items', ['reference' => $payment->reference]);
            return;
        }

        DB::transaction(function () use ($sellId, $items, $payment, &$meta) {
            $sell = \App\Models\Sell::find($sellId);
            if ($sell) {
                $sell->update([
                    'order_status' => 6,
                    'status' => 1,
                    'total_paid' => (float) $sell->total_paid + (float) $payment->amount,
                    'total_due' => 0,
                ]);
            }

            foreach ($items as $it) {
                $pid = (int) ($it['product_id'] ?? 0);
                $qty = (int) ($it['quantity'] ?? 0);
                if ($pid > 0 && $qty > 0) {
                    \App\Models\Product::where('id', $pid)->decrement('available_quantity', $qty);
                }
            }

            $meta['processed'] = true;
            $meta['processed_at'] = now()->toDateTimeString();
            $payment->meta = $meta;
            $payment->save();
        });
    }
}