<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_subscription_id',
        'amount',
        'currency',
        'status',
        'payment_gateway',
        'transaction_reference',
        'gateway_response',
        'payment_method',
        'payment_provider',
        'checkout_url',
        'receipt_url',
        'paid_at',
        'raw_response',
        'meta',
        'applied_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'meta' => 'array',
        'applied_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(BranchSubscription::class, 'branch_subscription_id');
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'completed'], true);
    }
    public function isCreditsTopup(): bool
    {
        return ($this->meta['type'] ?? null) === 'employee_credits';
    }
    public function alreadyApplied(): bool
    {
        return !is_null($this->applied_at);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'transaction_reference', 'invoice_number');
    }
}
