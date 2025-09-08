<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'amount_due',
        'amount_paid',
        'currency',
        'due_date',
        'status', // Tracks status: pending, paid, overdue, canceled
        'issued_at',
        'paid_at',
        'period_start',
        'period_end',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
