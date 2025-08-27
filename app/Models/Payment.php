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
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(BranchSubscription::class, 'branch_subscription_id');
    }
}
