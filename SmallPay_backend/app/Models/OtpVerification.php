<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier', // peut être email ou phone
        'code',
        'expires_at',
        'verified_at',
        'attempts',
        'method', // 'email' ou 'sms'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                     ->whereNull('verified_at');
    }

    public function scopeByIdentifier($query, $identifier)
    {
        return $query->where('identifier', $identifier);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('identifier', $email)->where('method', 'email');
    }

    public function scopeByPhone($query, $phone)
    {
        return $query->where('identifier', $phone)->where('method', 'sms');
    }

    // Helpers
    public function isExpired()
    {
        return now()->isAfter($this->expires_at);
    }

    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    public function isValid()
    {
        return !$this->isExpired() && !$this->isVerified();
    }

    public function markAsVerified()
    {
        $this->update(['verified_at' => now()]);
    }

    public function incrementAttempts()
    {
        $this->increment('attempts');
    }

    public function hasExceededAttempts()
    {
        return $this->attempts >= config('auth.otp.max_attempts', 5);
    }

    public function getRemainingAttempts()
    {
        return max(0, config('auth.otp.max_attempts', 5) - $this->attempts);
    }

    public function isForEmail()
    {
        return $this->method === 'email';
    }

    public function isForSms()
    {
        return $this->method === 'sms';
    }
}