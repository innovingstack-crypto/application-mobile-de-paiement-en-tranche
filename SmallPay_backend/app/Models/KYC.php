<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KYC extends Model
{
    use HasFactory;

    protected $table = 'kycs';

    protected $fillable = [
        'user_id',
        'data',
        'client_id_number',
        'client_phone',
        'guarantor_name',
        'guarantor_phone',
        'id_front_path',
        'id_back_path',
        'client_photo_path',
        'signed_document_path',
        'guarantor_id_front_path',
        'guarantor_id_back_path',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'rejected_at',
        'client_verified',
        'guarantor_verified',
    ];

    protected $casts = [
        'data' => 'json',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'client_verified' => 'boolean',
        'guarantor_verified' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===== ACCESSORS FOR DATA =====
    /**
     * Get client data from JSON
     */
    public function getClientAttribute()
    {
        return $this->data['client'] ?? null;
    }

    /**
     * Get guarantor data from JSON
     */
    public function getGuarantorAttribute()
    {
        return $this->data['guarantor'] ?? null;
    }

    /**
     * Get signed document from JSON
     */
    public function getSignedDocumentAttribute()
    {
        return $this->data['signedDocument'] ?? null;
    }

    /**
     * Get client documents from JSON
     */
    public function getClientDocumentsAttribute()
    {
        return $this->data['client']['documents'] ?? null;
    }

    /**
     * Get guarantor documents from JSON
     */
    public function getGuarantorDocumentsAttribute()
    {
        return $this->data['guarantor']['documents'] ?? null;
    }

    // ===== SCOPES =====
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    // ===== HELPER METHODS =====
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isUnderReview()
    {
        return $this->status === 'under_review';
    }

    public function approve($adminId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'client_verified' => true,
            'guarantor_verified' => true,
        ]);
    }

    public function reject($adminId, $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);
    }
}
