<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $client;
    protected $config;
    protected $driver;

    public function __construct()
    {
        $this->client = new Client([
            // ATTENTION: 'verify' => false désactive la vérification SSL.
            // C'est un risque de sécurité en production.
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
        $this->driver = config('sms.default', 'alooh');
        $this->config = config('sms.drivers.' . $this->driver);
    }

    /**
     * Send SMS via the configured provider
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    public function sendSms(string $phone, string $message): array
    {
        try {
            if ($this->driver === 'alooh') {
                return $this->sendAloohSms($phone, $message);
            } elseif ($this->driver === 'camoo') {
                return $this->sendCamooSms($phone, $message);
            }

            return [
                'success' => false,
                'message' => 'SMS driver not configured',
                'error' => 'Invalid SMS driver: ' . $this->driver
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phone,
                'driver' => $this->driver,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS. Veuillez réessayer.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via AloohSMS
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function sendAloohSms(string $phone, string $message): array
    {
        try {
            // Log de la requête (mis à jour avec 'senderName')
            Log::info('Sending SMS via AloohSMS', [
                'phone' => $phone,
                'formatted_phone' => $this->formatPhoneNumber($phone),
                'message' => $message,
                'api_url' => $this->config['api_url'],
                'senderName' => $this->config['senderName']
            ]);

            $response = $this->client->post($this->config['api_url'], [
                // NOUVELLE MÉTHODE D'AUTH (Basic Auth)
                'auth' => [
                    $this->config['username'],
                    $this->config['password']
                ],

                'headers' => [
                   'Content-Type' => 'application/json',
                   'Accept' => 'application/json',
                ],

                // NOUVEAU CORPS JSON (selon l'exemple d'AloohSMS)
                'json' => [
                    'senderName' => $this->config['senderName'],
                    'number' => $this->formatPhoneNumber($phone),
                    'message' => $message,
                ],
                'timeout' => $this->config['timeout'] ?? 30,
            ]);

            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            Log::info('AloohSMS Response', [
                'status_code' => $response->getStatusCode(),
                'response_body' => $responseBody,
                'parsed_data' => $data
            ]);

            if ($response->getStatusCode() === 200) {
                // AloohSMS peut retourner différents formats de réponse
                $isSuccess = isset($data['success']) ? $data['success'] :
                    (isset($data['status']) && ($data['status'] === 'success' || $data['status'] == 200)) ||
                    (isset($data['code']) && $data['code'] == 200);

                if ($isSuccess) {
                    Log::info('SMS sent successfully via AloohSMS', [
                        'phone' => $phone,
                        'message_id' => $data['data']['message_id'] ?? $data['message_id'] ?? null,
                        'response' => $data
                    ]);

                    return [
                        'success' => true,
                        'message' => 'SMS envoyé avec succès via AloohSMS',
                        'data' => $data['data'] ?? $data
                    ];
                }
            }

            // Si le code n'est pas 200 ou si $isSuccess est false
            Log::warning('AloohSMS returned failure', [
                'phone' => $phone,
                'response_body' => $data,
            ]);

            return [
                'success' => false,
                'message' => $data['message'] ?? $data['error'] ?? 'Échec de l\'envoi du SMS via AloohSMS',
                'error' => $data
            ];

        } catch (RequestException $e) {
            $responseContents = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('AloohSMS sending failed (RequestException)', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'response_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
                'response_body' => $responseContents
            ]);

            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 401) {
                 $errorMessage = 'Identifiants AloohSMS invalides. Vérifiez ALOOH_SMS_USERNAME et ALOOH_SMS_PASSWORD.';
             } else if (str_contains($e->getMessage(), 'Failed to connect')) {
                $errorMessage = 'Impossible de se connecter au service AloohSMS.';
            } else {
                 $errorMessage = 'Service AloohSMS temporairement indisponible.';
             }

            return [
                'success' => false,
                'message' => $errorMessage,
                'error_details' => $responseContents ?? $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('AloohSMS sending failed (General Exception)', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS via AloohSMS. Veuillez réessayer.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via CamooSMS
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function sendCamooSms(string $phone, string $message): array
    {
        try {
            // Log de la requête pour Camoo
            Log::info('Sending SMS via CamooSMS', [
                'phone' => $phone,
                'formatted_phone' => $this->formatPhoneNumber($phone),
                'message' => $message,
                'api_url' => $this->config['api_url'],
                'sender' => $this->config['sender']
            ]);

            $response = $this->client->post($this->config['api_url'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                ],

                'json' => [
                    'sender' => $this->config['sender'],
                    'recipient' => $this->formatPhoneNumber($phone),
                    'message' => $message,
                ],
                'timeout' => $this->config['timeout'] ?? 30,
            ]);

            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            Log::info('CamooSMS Response', [
                'status_code' => $response->getStatusCode(),
                'response_body' => $responseBody,
                'parsed_data' => $data
            ]);

            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                // Camoo retourne généralement un format standard
                $isSuccess = isset($data['status']) && $data['status'] === 'success';

                if ($isSuccess) {
                    Log::info('SMS sent successfully via CamooSMS', [
                        'phone' => $phone,
                        'message_id' => $data['data']['message_id'] ?? null,
                        'response' => $data
                    ]);

                    return [
                        'success' => true,
                        'message' => 'SMS envoyé avec succès via CamooSMS',
                        'data' => $data['data'] ?? $data
                    ];
                }
            }

            // Si le code n'est pas 200/201 ou si $isSuccess est false
            Log::warning('CamooSMS returned failure', [
                'phone' => $phone,
                'response_body' => $data,
            ]);

            return [
                'success' => false,
                'message' => $data['message'] ?? $data['error'] ?? 'Échec de l\'envoi du SMS via CamooSMS',
                'error' => $data
            ];

        } catch (RequestException $e) {
            $responseContents = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('CamooSMS sending failed (RequestException)', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'response_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
                'response_body' => $responseContents
            ]);

            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 401) {
                 $errorMessage = 'Clé API CamooSMS invalide. Vérifiez CAMOO_SMS_API_KEY.';
             } else if (str_contains($e->getMessage(), 'Failed to connect')) {
                $errorMessage = 'Impossible de se connecter au service CamooSMS.';
            } else {
                 $errorMessage = 'Service CamooSMS temporairement indisponible.';
             }

            return [
                'success' => false,
                'message' => $errorMessage,
                'error_details' => $responseContents ?? $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('CamooSMS sending failed (General Exception)', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS via CamooSMS. Veuillez réessayer.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send OTP SMS
     * (Aucun changement nécessaire ici)
     *
     * @param string $phone
     * @param string $otp
     * @param string $type
     * @return array
     */
    public function sendOtpSms(string $phone, string $otp, string $type = 'verification'): array
    {
        $message = $this->getOtpMessage($otp, $type);
        return $this->sendSms($phone, $message);
    }

    /**
     * Format phone number for international format
     * (Aucun changement nécessaire ici)
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (assuming Cameroon +237)
        if (strlen($phone) === 9 && !str_starts_with($phone, '237')) {
            $phone = '237' . $phone;
        }

        // AloohSMS attend le format international sans le +
        return $phone;
    }

    /**
     * Get OTP message based on type
     * (Aucun changement nécessaire ici)
     *
     * @param string $otp
     * @param string $type
     * @return string
     */
    protected function getOtpMessage(string $otp, string $type): string
    {
        $messages = [
            'verification' => "Votre code de vérification GODLOVESHOP est: {$otp}. Ce code expire dans 10 minutes. Ne partagez jamais ce code.",
            'password_reset' => "Votre code de réinitialisation de mot de passe GODLOVESHOP est: {$otp}. Ce code expire dans 10 minutes. Ne partagez jamais ce code.",
            'registration' => "Bienvenue sur GODLOVESHOP! Votre code de vérification est: {$otp}. Ce code expire dans 10 minutes. Ne partagez jamais ce code.",
        ];

        return $messages[$type] ?? $messages['verification'];
    }
}
