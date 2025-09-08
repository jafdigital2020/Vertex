<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
      'tenant_id',
      'payment_transaction_id',
      'subscription_id',
      'event',
    ];

    // Tenant relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // PaymentTransaction relationship
    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    // Subscription relationship
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
