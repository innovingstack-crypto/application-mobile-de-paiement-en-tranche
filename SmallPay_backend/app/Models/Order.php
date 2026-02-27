<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'deposit_amount',
        'remaining_amount',
        'payment_duration',
        'majoration_rate',
        'status',
        'next_due_date',
        'payment_status',
        'payment_method',
        'deposit_reference',
        'deposit_paid_at',
        'total_interest',
        'is_kyc_required',
        'kyc_verified_at',
        'verified_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'payment_duration' => 'integer',
        'majoration_rate' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'next_due_date' => 'date',
        'deposit_paid_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function schedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function kyc()
    {
        return $this->user->kyc();
    }

    public function campayPayments()
    {
        return $this->hasMany(\App\Models\CampayPayment::class);
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithOverduePayments($query)
    {
        return $query->whereHas('schedules', function($q) {
            $q->where('status', 'overdue');
        });
    }

    // Helpers
    public function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr($this->id, -6));
    }

    public function calculateDepositAmount()
    {
        $itemsTotal = $this->items()->sum(DB::raw('price * quantity'));
        return $itemsTotal * 0.60;
    }

    public function calculateMonthlyPayment()
    {
        return $this->payment_duration > 0 ? $this->remaining_amount / $this->payment_duration : 0;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function getTotalPaidAmount()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getRemainingBalance()
    {
        return $this->remaining_amount - $this->getTotalPaidAmount();
    }

    public function getNextDueDate()
    {
        return $this->schedules()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first()?->due_date;
    }

    public function getOverdueSchedules()
    {
        return $this->schedules()
            ->where('status', 'overdue')
            ->orWhere(function($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '<', now());
            })
            ->get();
    }

    public function hasOverduePayments()
    {
        return $this->getOverdueSchedules()->count() > 0;
    }

    public function markAsActive()
    {
        $this->update(['status' => 'active']);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
    }
}