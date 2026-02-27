<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampayPayment extends Model
{
    use HasFactory;

    protected $table = 'campay_payments';

    protected $fillable = [
        'reference',
        'amount',
        'currency',
        'phone',
        'status',
        'description',
        'provider',
        'meta',
        'user_id',
        'order_id',
        'payment_schedule_id',
        'payment_type',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function schedule()
    {
        return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
    }

    // ===== SCOPES =====
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDeposit($query)
    {
        return $query->where('payment_type', 'deposit');
    }

    public function scopeInstallment($query)
    {
        return $query->where('payment_type', 'installment');
    }

    // ===== HELPERS =====
    public function isSuccess()
    {
        return $this->status === 'success';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isDeposit()
    {
        return $this->payment_type === 'deposit';
    }

    public function isInstallment()
    {
        return $this->payment_type === 'installment';
    }

    public function getErrorReason()
    {
        $meta = $this->meta ?? [];
        return $meta['error_reason'] ?? $meta['error_code'] ?? null;
    }
}
