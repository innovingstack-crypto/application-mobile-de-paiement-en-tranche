<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at',
        'verification_method',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function kyc()
    {
        return $this->hasOne(KYC::class);
    }

    public function approvedKYCs()
    {
        return $this->hasMany(KYC::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeUser($query)
    {
        return $query->where('role', 'user');
    }

    // Helpers
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isBlocked()
    {
        return $this->status === 'blocked';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isVerified()
    {
        return $this->is_verified;
    }

    public function usesEmailVerification()
    {
        return $this->verification_method === 'email';
    }

    public function usesSmsVerification()
    {
        return $this->verification_method === 'sms';
    }

    public function markAsVerified()
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    public function getVerificationTarget()
    {
        return $this->usesEmailVerification() ? $this->email : $this->phone;
    }

    /**
     * Générer un token de réinitialisation de mot de passe
     */
    public function generateResetToken()
    {
        $this->reset_token = Str::random(60);
        $this->reset_token_expires_at = now()->addHours(1);
        $this->save();
        
        return $this->reset_token;
    }

    /**
     * Vérifier si le token de réinitialisation est valide
     */
    public function isResetTokenValid($token)
    {
        return $this->reset_token === $token && 
               $this->reset_token_expires_at && 
               now()->isBefore($this->reset_token_expires_at);
    }

    /**
     * Invalider le token de réinitialisation
     */
    public function invalidateResetToken()
    {
        $this->reset_token = null;
        $this->reset_token_expires_at = null;
        $this->save();
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($newPassword)
    {
        $this->password = Hash::make($newPassword);
        $this->invalidateResetToken();
        $this->save();
    }
}