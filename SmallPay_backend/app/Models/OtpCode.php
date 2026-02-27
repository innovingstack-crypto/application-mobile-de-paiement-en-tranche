<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier', // email ou téléphone
        'code',
        'type', // type d'OTP (registration, password_reset, verification)
        'channel', // canal (email ou sms)
        'expires_at',
        'verified_at',
        'attempts',
        'user_id', // ID utilisateur optionnel
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Générer un code OTP
     *
     * @param string $identifier
     * @param string $type
     * @param string $channel
     * @param int|null $userId
     * @return OtpCode
     */
    public static function generate(string $identifier, string $type, string $channel, ?int $userId = null): OtpCode
    {
        // Supprimer les anciens codes non expirés pour cet identifiant et type
        self::where('identifier', $identifier)
            ->where('type', $type)
            ->where('channel', $channel)
            ->where(function($query) {
                $query->whereNull('verified_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->delete();

        // Générer un code aléatoire
        $codeLength = config('sms.otp.length', 6);
        $code = str_pad(random_int(0, pow(10, $codeLength) - 1), $codeLength, '0', STR_PAD_LEFT);

        // Créer un nouveau code OTP
        return self::create([
            'identifier' => $identifier,
            'code' => $code,
            'type' => $type,
            'channel' => $channel,
            'expires_at' => now()->addMinutes(config('sms.otp.expiry_minutes', 10)),
            'attempts' => 0,
            'user_id' => $userId,
        ]);
    }

    /**
     * Vérifier un code OTP
     *
     * @param string $identifier
     * @param string $code
     * @param string $type
     * @return bool
     */
    public static function verify(string $identifier, string $code, string $type): bool
    {
        $otp = self::where('identifier', $identifier)
            ->where('code', $code)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if (!$otp) {
            return false;
        }

        // Marquer comme vérifié
        $otp->verified_at = now();
        $otp->save();

        return true;
    }

    /**
     * Vérifier si un OTP est valide pour un identifiant
     *
     * @param string $identifier
     * @param string $type
     * @return bool
     */
    public static function isValid(string $identifier, string $type): bool
    {
        return self::where('identifier', $identifier)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->exists();
    }

    /**
     * Nettoyer les codes OTP expirés
     *
     * @return int
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<=', now())
            ->orWhereNotNull('verified_at')
            ->delete();
    }

    /**
     * Incrémenter le nombre de tentatives
     */
    public function incrementAttempts()
    {
        $this->attempts++;
        $this->save();
    }

    /**
     * Vérifier si le nombre maximum de tentatives est atteint
     *
     * @return bool
     */
    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= config('sms.otp.max_attempts', 3);
    }

    /**
     * Obtenir le nombre de tentatives restantes
     *
     * @return int
     */
    public function getRemainingAttempts(): int
    {
        return max(0, config('sms.otp.max_attempts', 3) - $this->attempts);
    }

    /**
     * Vérifier si le code est expiré
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Vérifier si le code est déjà vérifié
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}