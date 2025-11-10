<?php

namespace App\Models;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'upgrade_plan_id', // New field to store the new plan ID for plan upgrades
        'invoice_type',
        'billing_cycle', // New field for billing cycle (monthly/yearly)
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
        'consolidated_into_invoice_id',
        'unused_overage_count',
        'unused_overage_amount',
        'gross_overage_count',
        'gross_overage_amount',
        'implementation_fee',
        'vat_amount',
        'vat_percentage',
        'subtotal',
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
        'implementation_fee' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function upgradePlan()
    {
        return $this->belongsTo(Plan::class, 'upgrade_plan_id');
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

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
