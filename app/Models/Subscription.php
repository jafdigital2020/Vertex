<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'amount_paid',
        'payment_status',
        'status', // Tracks status: active, expired, trial, canceled
        'subscription_start',
        'subscription_end',
        'trial_start',
        'trial_end',
        'renewed_at',
        'billing_cycle', // Monthly or Yearly
        'next_renewal_date'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function isTrial()
    {
        return $this->status === 'trial';
    }

    public function isCanceled()
    {
        return $this->status === 'canceled';
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }
}
