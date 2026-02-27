<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Dispatch OTP to user via selected channel
     * * @param string $identifier (Email or Phone)
     * @param string $type (registration, password_reset, etc.)
     * @param string $method (email, sms)
     * @param int|null $userId
     * @param array $payload (Must contain 'code')
     * @return array
     */
    public function sendOtp(string $identifier, string $type, string $method, ?int $userId = null, array $payload = []): array
    {
        // Validation basique
        if (empty($payload['code'])) {
            Log::error('OTP Service: Code is missing in payload');
            return ['success' => false, 'message' => 'Code OTP manquant.'];
        }

        $code = $payload['code'];

        if ($method === 'sms') {
            return $this->sendSmsOtp($identifier, $code, $type);
        } elseif ($method === 'email') {
            return $this->sendEmailOtp($identifier, $code, $type);
        }

        return [
            'success' => false,
            'message' => 'Méthode d\'envoi non supportée.'
        ];
    }

    /**
     * Send OTP via SMS
     */
    protected function sendSmsOtp(string $phone, string $code, string $type): array
    {
        if (!$this->smsService) {
            Log::warning("SMS Service not configured, fallback to log for phone: $phone");
            return [
                'success' => false, 
                'message' => 'Service SMS indisponible.'
            ];
        }

        // On délègue l'envoi réel au SmsService
        return $this->smsService->sendOtpSms($phone, $code, $type);
    }

    /**
     * Send OTP via Email
     */
    protected function sendEmailOtp(string $email, string $code, string $type): array
    {
        try {
            $subject = $this->getEmailSubject($type);
            $message = $this->getEmailMessage($code, $type);

            // Envoi de l'email
            // Note: Pour un projet pro, envisagez de créer des classes Mailable (php artisan make:mail OtpMail)
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                     ->subject($subject);
            });

            return [
                'success' => true,
                'message' => 'Code envoyé par email.'
            ];

        } catch (\Exception $e) {
            Log::error('OTP email sending failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Échec de l\'envoi de l\'email.'
            ];
        }
    }

    /**
     * Get email subject based on type
     */
    protected function getEmailSubject(string $type): string
    {
        return match ($type) {
            'registration' => 'Bienvenue ! Votre code de vérification',
            'password_reset' => 'Réinitialisation de votre mot de passe',
            default => 'Votre code de vérification GODLOVESHOP',
        };
    }

    /**
     * Get email message based on type
     */
    protected function getEmailMessage(string $code, string $type): string
    {
        $commonFooter = "\n\nCe code expire dans 10 minutes.\nNe le partagez avec personne.\n\nL'équipe GODLOVESHOP";

        return match ($type) {
            'registration' => "Bienvenue sur GODLOVESHOP!\n\nPour finaliser votre inscription, voici votre code : {$code}" . $commonFooter,
            'password_reset' => "Vous avez demandé à réinitialiser votre mot de passe.\n\nVoici votre code de sécurité : {$code}" . $commonFooter,
            default => "Voici votre code de vérification : {$code}" . $commonFooter,
        };
    }
}