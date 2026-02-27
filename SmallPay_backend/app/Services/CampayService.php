<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Order;

class CampayService
{
    private string $apiKey;
    private string $username;
    private string $password;
    private string $baseUrl = 'https://api.campay.net/v1';
    private string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('campay.api_key');
        $this->username = config('campay.username');
        $this->password = config('campay.password');
        $this->webhookSecret = config('campay.webhook_secret', '');
    }

    /**
     * Initier un paiement avec contexte SmallPay
     * Accepte User, Order et type de paiement
     */
    public function initiateSmallpayPayment(User $user, Order $order, string $type = 'deposit'): array
    {
        $paymentFlowService = new PaymentFlowService();
        
        // Préparer les détails du paiement selon le type
        $paymentDetails = match($type) {
            'deposit' => $paymentFlowService->prepareDepositPayment($user, $order),
            'installment' => $paymentFlowService->prepareMonthlyPayment($user, $order),
            default => throw new \InvalidArgumentException("Type de paiement invalide: {$type}")
        };

        if (isset($paymentDetails['error'])) {
            return [
                'success' => false,
                'error' => 'PAYMENT_PREPARATION_FAILED',
                'message' => $paymentDetails['error'],
            ];
        }

        try {
            $resp = $this->initiateCollect([
                'amount' => $paymentDetails['amount'],
                'from' => $paymentDetails['phone'],
                'currency' => $paymentDetails['currency'],
                'description' => $paymentDetails['description'],
                'reference' => \Illuminate\Support\Str::uuid()->toString(),
            ]);

            if (!($resp['success'] ?? false)) {
                Log::warning('campay_initiate_failed', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'type' => $type,
                    'error' => $resp['error'] ?? $resp,
                ]);

                return [
                    'success' => false,
                    'error' => $resp['error'] ?? 'COLLECT_FAILED',
                    'message' => $resp['message'] ?? 'Erreur lors de l\'initiation du paiement',
                ];
            }

            Log::info('campay_initiate_success', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'type' => $type,
                'reference' => $resp['reference'],
                'amount' => $paymentDetails['amount'],
            ]);

            return [
                'success' => true,
                'reference' => $resp['reference'],
                'amount' => $paymentDetails['amount'],
                'currency' => $paymentDetails['currency'],
                'phone' => $this->maskPhoneNumber($paymentDetails['phone']),
                'message' => 'Veuillez approuver le paiement sur votre téléphone',
                'redirect_to' => 'campay_payment_screen',
                'redirect_url' => config('app.url') . "/payment/processing/{$resp['reference']}",
                'payment_details' => $paymentDetails,
            ];
        } catch (\Throwable $e) {
            Log::error('campay_initiate_exception', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'CONNECTION_ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initier une collecte auprès de Campay
     */
    public function initiateCollect(array $payload): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/collect/", $payload)
                ->throw();

            return $response->json();
        } catch (\Exception $e) {
            Log::error('campay_collect_exception', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer l'état d'une transaction
     */
    public function getTransactionStatus(string $reference): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->baseUrl}/get_transaction_status/?reference={$reference}")
                ->throw();

            return $response->json();
        } catch (\Exception $e) {
            Log::error('campay_status_exception', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'STATUS_CHECK_FAILED',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier la signature webhook de Campay
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('campay_webhook_secret_not_configured');
            return true; // Désactiver la vérification si pas de secret
        }

        // Créer une signature basée sur le payload
        $expectedSignature = hash_hmac(
            'sha256',
            json_encode($payload),
            $this->webhookSecret
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Récupérer la raison d'une erreur Campay
     */
    public static function getErrorReason(string $errorCode): string
    {
        return match($errorCode) {
            'ER001' => 'Erreur de paramètre',
            'ER002' => 'Authentification échouée',
            'ER003' => 'Solde insuffisant',
            'ER004' => 'Transaction non trouvée',
            'ER005' => 'Transaction déjà traitée',
            'ER301' => 'Solde insuffisant',
            'ER302' => 'Numéro de téléphone invalide',
            'ER303' => 'Montant invalide',
            'ER304' => 'Description manquante',
            'ER305' => 'Monnaie invalide',
            'INVALID_REQUEST' => 'Requête invalide',
            'NOT_FOUND' => 'Transaction non trouvée',
            'NETWORK_ERROR' => 'Erreur de réseau',
            'TIMEOUT' => 'Délai d\'attente dépassé',
            default => "Erreur: {$errorCode}",
        };
    }

    /**
     * Masquer le numéro de téléphone pour l'affichage
     */
    private function maskPhoneNumber(string $phone): string
    {
        // Afficher: +237 6XX XXX XXXX
        if (preg_match('/^\+237(\d{2})(\d{3})(\d{4})$/', $phone, $matches)) {
            return "+237 {$matches[1]}X XXX {$matches[3]}";
        }

        // Affichage par défaut: masquer les derniers 6 chiffres
        return substr($phone, 0, -6) . '......';
    }

    /**
     * Configuration Campay depuis le fichier config
     */
    public function getConfig(): array
    {
        return [
            'api_key' => $this->apiKey,
            'username' => $this->username,
            'base_url' => $this->baseUrl,
            'currency' => config('campay.currency', 'XAF'),
        ];
    }
}
