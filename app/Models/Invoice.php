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
        'invoice_type',
        'license_overage_count', // New field to track if invoice is for license overage
        'license_overage_rate',
        'license_overage_amount',
        'subscription_amount',
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

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'issued_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'subscription_amount' => 'decimal:2',
        'license_overage_amount' => 'decimal:2',
        'license_overage_rate' => 'decimal:2',
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

    public function latestTransaction()
    {
        return $this->hasOne(PaymentTransaction::class)->latest();
    }

    public function scopeLicenseOverage($query)
    {
        return $query->whereIn('invoice_type', ['license_overage', 'combo']);
    }

    public function scopeSubscriptionOnly($query)
    {
        return $query->where('invoice_type', 'subscription');
    }
}
