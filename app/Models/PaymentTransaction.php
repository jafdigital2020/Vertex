<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'subscription_id',
        'payment_gateway',
        'transaction_reference',
        'amount',
        'currency',
        'status', // Tracks status: pending, completed, failed
        'paid_at',
        'raw_request',
        'raw_response',
        'retry_count',
        'last_status_check',
    ];

    protected $casts = [
        'raw_request' => 'array',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'last_status_check' => 'datetime',
    ];

    // Relationships to Subscription
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Relationship to Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Relationship to PaymentHistory
    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeNeedsStatusCheck($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('last_status_check')
                    ->orWhere('last_status_check', '<', now()->subMinutes(5));
            });
    }

    // Helpers
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    public function markAsChecked()
    {
        $this->update(['last_status_check' => now()]);
    }
}
