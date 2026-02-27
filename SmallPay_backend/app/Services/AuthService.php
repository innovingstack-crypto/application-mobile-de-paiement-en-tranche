<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpVerification;
use App\Exceptions\UnverifiedUserException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthService
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Inscrire un nouvel utilisateur
     */
    public function register($name, $email, $phone, $password, $verificationChannel)
    {
        // On vérifie l'existence avant la transaction pour éviter les erreurs SQL
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Cet email est déjà utilisé.']);
        }

        if ($phone && User::where('phone', $phone)->exists()) {
            throw ValidationException::withMessages(['phone' => 'Ce numéro de téléphone est déjà utilisé.']);
        }

        return DB::transaction(function () use ($name, $email, $phone, $password, $verificationChannel) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make($password),
                'role' => 'user', // Rôle par défaut
                'is_verified' => false,
                // On stocke la préférence de canal pour référence future
                // Assure-toi d'avoir une colonne 'verification_channel' ou similaire si tu veux persister ce choix
            ]);

            return $user;
        });
    }

    /**
     * Générer et envoyer un code OTP (Logique centrale)
     */
    public function requestOTP($identifier, $channel = 'email', $type = 'verification')
    {
        // 1. Nettoyage et Anti-Spam
        $query = OtpVerification::where('identifier', $identifier)
            ->where('method', $channel); // 'method' correspond à 'channel' (email/sms)

        $resendDelay = (int) config('auth.otp.resend_delay', 1);
        $recentOtp = (clone $query)->where('created_at', '>', now()->subMinutes($resendDelay))->first();

        if ($recentOtp) {
            $secondsRemaining = now()->diffInSeconds($recentOtp->created_at->addMinutes($resendDelay));
            throw new \Exception("Veuillez patienter {$secondsRemaining} secondes avant de demander un nouveau code.");
        }

        // Supprimer les anciens codes expirés ou non utilisés pour cet identifiant
        $query->delete();

        // 2. Génération du code
        $codeLength = (int) config('auth.otp.code_length', 6);
        // Utilisation de random_int pour la sécurité cryptographique
        $code = str_pad(random_int(0, pow(10, $codeLength) - 1), $codeLength, '0', STR_PAD_LEFT);

        // 3. Stockage en base
        // On essaie de récupérer l'ID user si possible pour lier l'OTP (optionnel selon ta structure DB)
        $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();
        
        $otp = OtpVerification::create([
            'identifier' => $identifier,
            'user_id' => $user ? $user->id : null,
            'method' => $channel,
            'code' => $code, // Hash le code si tu veux plus de sécurité: Hash::make($code)
            'type' => $type, // 'registration', 'password_reset', etc.
            'expires_at' => now()->addMinutes((int) config('auth.otp.expiration', 10)),
        ]);

        // 4. Envoi via le service de transport (Email/SMS)
        // Note: OtpService ne doit faire que l'envoi, pas la création DB ici
        $sendResult = $this->otpService->sendOtp(
            $identifier,
            $type,
            $channel,
            $user ? $user->id : null,
            ['code' => $code] // On passe le code explicitement au service d'envoi
        );

        if (!$sendResult['success']) {
            // Si l'envoi échoue, on supprime le code généré pour ne pas bloquer l'user
            $otp->delete();
            throw new \Exception($sendResult['message'] ?? 'Erreur lors de l\'envoi du code.');
        }

        return [
            'success' => true,
            'identifier' => $identifier,
            'expires_at' => $otp->expires_at
        ];
    }

    /**
     * Vérifier le code OTP (Méthode générique)
     * Retourne l'objet OTP si valide, sinon throw Exception
     */
    public function validateOtpCode($identifier, $code, $channel)
    {
        $otpRecord = OtpVerification::where('identifier', $identifier)
            ->where('method', $channel)
            ->first();

        if (!$otpRecord) {
            throw new \Exception('Aucun code de vérification trouvé pour cet identifiant.');
        }

        if ($otpRecord->expires_at < now()) {
            $otpRecord->delete();
            throw new \Exception('Le code de vérification a expiré.');
        }

        if ($otpRecord->code !== $code) {
             // Tu peux ajouter une logique d'incrémentation des tentatives ici
            throw new \Exception('Code de vérification invalide.');
        }

        return $otpRecord;
    }

    /**
     * Vérifier un compte utilisateur (Suite à inscription)
     */
    public function verifyUserAccount($identifier, $code, $channel)
    {
        // 1. Valider le code
        $otpRecord = $this->validateOtpCode($identifier, $code, $channel);

        // 2. Trouver et mettre à jour l'utilisateur
        $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();

        if (!$user) {
            throw new \Exception('Utilisateur introuvable.');
        }

        if ($user->is_verified) {
             // Si déjà vérifié, on nettoie juste l'OTP et on renvoie succès
            $otpRecord->delete();
            return ['user' => $user, 'already_verified' => true];
        }

        DB::transaction(function () use ($user, $otpRecord) {
            $user->update([
                'is_verified' => true,
                'email_verified_at' => now(), // Ou phone_verified_at selon le channel
                'status' => 'active'
            ]);
            
            // Supprimer le code utilisé
            $otpRecord->delete();
        });

        return ['user' => $user, 'already_verified' => false];
    }

    /**
     * Initialiser la procédure de reset de mot de passe
     */
    public function requestPasswordReset($identifier, $channel)
    {
        $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();

        // Sécurité : Si l'user n'existe pas, on fait semblant que ça a marché (Timing Attack prevention)
        // Mais pour l'UX, parfois on préfère dire "User not found". À toi de choisir.
        // Ici je retourne success false mais un message générique pour l'API.
        if (!$user) {
            // On log la tentative pour debug interne
            Log::info("Password reset requested for unknown user: {$identifier}");
            return [
                'success' => true, // On dit au front que c'est "envoyé"
                'message' => 'Si un compte existe avec cet identifiant, un code a été envoyé.'
            ];
        }

        // Utiliser la méthode centrale requestOTP avec le type 'password_reset'
        $otpResult = $this->requestOTP($identifier, $channel, 'password_reset');

        return [
            'success' => true,
            'message' => 'Code de réinitialisation envoyé.',
            'identifier' => $identifier,
            'method' => $channel,
            'expires_at' => $otpResult['expires_at']
        ];
    }

    /**
     * Effectuer le changement de mot de passe
     */
    public function resetPassword($identifier, $code, $newPassword, $channel = 'email')
    {
        // 1. Valider le code
        $otpRecord = $this->validateOtpCode($identifier, $code, $channel);

        // 2. Vérifier le type (optionnel mais recommandé)
        if ($otpRecord->type && $otpRecord->type !== 'password_reset') {
             throw new \Exception('Ce code n\'est pas valide pour une réinitialisation de mot de passe.');
        }

        $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();

        if (!$user) {
            throw new \Exception('Utilisateur introuvable.');
        }

        // 3. Changer le mot de passe
        $user->forceFill([
            'password' => Hash::make($newPassword)
        ])->setRememberToken(Str::random(60));

        $user->save();

        // 4. Supprimer le code utilisé
        $otpRecord->delete();

        return ['success' => true, 'message' => 'Mot de passe modifié avec succès.'];
    }

    /**
     * Login utilisateur normal (avec email/password)
     */
    public function login($email, $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Identifiants incorrects.');
        }

        // Vérifier si le compte est bloqué
        if ($user->status === 'blocked') {
            throw new \Exception('Votre compte a été bloqué. Veuillez contacter le support.');
        }

        // Vérifier que l'utilisateur est vérifié
        if (!$user->is_verified) {
            // Déterminer la méthode de vérification (email par défaut, téléphone si disponible)
            $verificationMethod = $user->phone ? 'sms' : 'email';
            $identifier = $user->phone ? $user->phone : $user->email;
            throw new UnverifiedUserException($identifier, $verificationMethod);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Login Admin
     */
    public function adminLogin($email, $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Identifiants incorrects.');
        }

        // Vérification si le compte est bloqué
        if ($user->status === 'blocked') {
            throw new \Exception('Votre compte a été bloqué. Veuillez contacter le support.');
        }

        // Vérification du rôle (adapter selon ta logique de rôles : 'admin', 'superadmin', etc.)
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            throw new \Exception('Accès non autorisé.');
        }

        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
    
    /**
     * Refresh Token
     */
    public function refreshToken()
    {
        $user = Auth::user();
        
        if (!$user) {
            throw new \Exception('Utilisateur non authentifié.');
        }
        
        // Option 1: Supprimer l'ancien token (Rotation stricte)
        // $user->currentAccessToken()->delete(); 
        
        // Option 2: Créer simplement un nouveau
        return $user->createToken('refresh_token')->plainTextToken;
    }
}