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
       'paid_at'
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
}
