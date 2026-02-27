<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Models\OtpVerification;
use App\Models\User;
use App\Exceptions\UnverifiedUserException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Inscription d'un utilisateur
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|string|same:password',
            'verification_method' => 'required|in:email,sms'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 1. Créer l'utilisateur
            $user = $this->authService->register(
                $request->name,
                $request->email,
                $request->phone,
                $request->password,
                $request->verification_method
            );

            // 2. Envoyer le code OTP immédiatement
            $identifier = ($request->verification_method === 'email') ? $request->email : $request->phone;
            $otpResult = $this->authService->requestOTP($identifier, $request->verification_method, 'registration');

            return response()->json([
                'message' => 'Utilisateur créé avec succès. Veuillez vérifier votre compte.',
                'user' => $user,
                'otp_info' => $otpResult
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Vérification du compte (OTP)
     */
    public function verifyAccount(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string', // Email ou Phone
            'code' => 'required|string',
            'channel' => 'required|in:email,sms'
        ]);

        try {
            $result = $this->authService->verifyUserAccount(
                $request->identifier,
                $request->code,
                $request->channel
            );

            // Générer le token après vérification réussie
            $token = $result['user']->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Compte vérifié avec succès.',
                'user' => $result['user'],
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Vérifier le code OTP pour inscription/vérification de compte
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string', // Email ou Phone
            'code' => 'required|string',
            'method' => 'required|in:email,sms'
        ]);

        try {
            $result = $this->authService->verifyUserAccount(
                $request->identifier,
                $request->code,
                $request->method
            );

            // Générer le token après vérification réussie
            $token = $result['user']->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Compte vérifié avec succès.',
                'user' => $result['user'],
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Vérifier le code OTP pour réinitialisation de mot de passe
     */
    public function verifyPasswordResetOTP(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'code' => 'required|string',
            'method' => 'required|in:email,sms'
        ]);

        try {
            // Juste vérifier que le code est valide, ne pas marquer comme vérifié
            $otpRecord = $this->authService->validateOtpCode(
                $request->identifier,
                $request->code,
                $request->method
            );

            return response()->json([
                'message' => 'Code OTP valide. Vous pouvez maintenant réinitialiser votre mot de passe.',
                'identifier' => $request->identifier,
                'method' => $request->method
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Vérifier le statut de l'OTP
     */
    public function checkOTPStatus(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'method' => 'required|in:email,sms'
        ]);

        try {
            $otpRecord = OtpVerification::where('identifier', $request->identifier)
                ->where('method', $request->method)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Aucun code OTP trouvé'
                ], 200);
            }

            if ($otpRecord->expires_at < now()) {
                $otpRecord->delete();
                return response()->json([
                    'exists' => false,
                    'expired' => true,
                    'message' => 'Le code OTP a expiré'
                ], 200);
            }

            return response()->json([
                'exists' => true,
                'expires_at' => $otpRecord->expires_at
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Renvoyer un code OTP pour vérification de compte (après tentative de connexion)
     */
    public function resendVerificationOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'method' => 'required|in:email,sms'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->authService->requestOTP(
                $request->identifier,
                $request->method,
                'registration'
            );

            return response()->json([
                'message' => 'Un nouveau code OTP a été envoyé.',
                'expires_at' => $result['expires_at']
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Demander un nouveau code OTP (Renvoi)
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'channel' => 'required|in:email,sms',
            'type' => 'nullable|string' // 'registration' ou 'password_reset'
        ]);

        try {
            $result = $this->authService->requestOTP(
                $request->identifier,
                $request->channel,
                $request->type ?? 'verification'
            );

            return response()->json([
                'message' => 'Un nouveau code a été envoyé.',
                'expires_at' => $result['expires_at']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Demander un code OTP pour réinitialiser le mot de passe (API)
     */
    public function requestPasswordReset(Request $request): JsonResponse
    {
        \Log::info('requestPasswordReset called with:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'method' => 'required|in:email,sms'
        ], [
            'identifier.required' => 'L\'identifiant (email ou téléphone) est requis.',
            'method.required' => 'La méthode est requise.',
            'method.in' => 'La méthode doit être "email" ou "sms".'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->authService->requestPasswordReset($request->identifier, $request->method);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Mot de passe oublié : Demande de code (Web)
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'channel' => 'required|in:email,sms'
        ]);

        try {
            $result = $this->authService->requestPasswordReset($request->identifier, $request->channel);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Réinitialiser le mot de passe avec le code (API)
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'code' => 'required|string',
            'password' => 'required|string|min:8',
            'method' => 'required|in:email,sms'
        ], [
            'code.required' => 'Le code OTP est requis.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit avoir au minimum 8 caractères.',
            'method.required' => 'La méthode est requise.',
            'method.in' => 'La méthode doit être "email" ou "sms".'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->authService->resetPassword(
                $request->identifier,
                $request->code,
                $request->password,
                $request->method
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Vérifier la disponibilité d'un email ou téléphone
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|required|email',
            'phone' => 'sometimes|required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $result = [
                'email_available' => true,
                'phone_available' => true
            ];

            // Vérifier l'email
            if ($request->has('email')) {
                $emailExists = User::where('email', $request->email)->exists();
                $result['email_available'] = !$emailExists;
            }

            // Vérifier le téléphone
            if ($request->has('phone')) {
                $phoneExists = User::where('phone', $request->phone)->exists();
                $result['phone_available'] = !$phoneExists;
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Connexion utilisateur (Email/Password)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        try {
            $result = $this->authService->login($request->email, $request->password);
            return response()->json($result);
        } catch (UnverifiedUserException $e) {
            // Retourner les infos de vérification pour rediriger le user vers l'OTP
            return response()->json([
                'message' => $e->getMessage(),
                'unverified' => true,
                'identifier' => $e->identifier,
                'method' => $e->verificationMethod
            ], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Connexion Admin (Email/Password classique)
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        try {
            $result = $this->authService->adminLogin($request->email, $request->password);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    /**
     * Infos utilisateur connecté
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}