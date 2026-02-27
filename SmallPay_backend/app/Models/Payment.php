<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'schedule_id',
        'method',
        'transaction_id',
        'amount',
        'status',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function schedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    // Helpers
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);

        // Marquer l'échéance comme payée si elle existe
        if ($this->schedule) {
            $this->schedule->update(['status' => 'paid']);
        }
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function getMethodLabel()
    {
        return match($this->method) {
            'MTN' => 'Mobile Money MTN',
            'ORANGE' => 'Mobile Money Orange',
            'CARD' => 'Carte bancaire',
            default => $this->method
        };
    }
}