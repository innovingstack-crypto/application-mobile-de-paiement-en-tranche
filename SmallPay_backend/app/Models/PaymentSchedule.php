<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'due_date',
        'amount',
        'installment_number',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'installment_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now())->where('status', 'pending');
    }

    public function scopeDue($query)
    {
        return $query->where('due_date', '<=', now())->where('status', 'pending');
    }

    public function scopeOverdueSchedules($query)
    {
        return $query->where('due_date', '<', now())->where('status', 'pending');
    }

    // Helpers
    public function isOverdue()
    {
        return $this->status === 'overdue' || (now()->isAfter($this->due_date) && $this->status !== 'paid');
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function markAsOverdue()
    {
        if ($this->status !== 'paid') {
            $this->update(['status' => 'overdue']);
        }
    }

    public function markAsPaid()
    {
        $this->update(['status' => 'paid']);
    }

    public function getDaysUntilDue()
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function isUpcoming()
    {
        return $this->getDaysUntilDue() > 0 && $this->status !== 'paid';
    }

    public function isDue()
    {
        return now()->isAfter($this->due_date) && $this->status !== 'paid';
    }

    public function getTotalPaidForSchedule()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }
}