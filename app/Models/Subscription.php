<?php

namespace App\Models;

use Carbon\Carbon;
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
        'next_renewal_date',
        'active_license', // New field to track active licenses
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

    /**
     * Get current subscription period dates
     */
    public function getCurrentPeriod()
    {
        $start = $this->subscription_start ?
            Carbon::parse($this->subscription_start) :
            Carbon::parse($this->next_renewal_date)->subMonth();

        $end = Carbon::parse($this->next_renewal_date);

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString()
        ];
    }

    /**
     * Get next subscription period dates
     */
    public function getNextPeriod()
    {
        $nextRenewal = Carbon::parse($this->next_renewal_date);

        $start = $nextRenewal;
        $end = $this->billing_cycle === 'yearly' ?
            $nextRenewal->copy()->addYear() :
            $nextRenewal->copy()->addMonth();

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString()
        ];
    }

    /**
     * Check if date falls within current subscription period
     */
    public function isWithinCurrentPeriod($date)
    {
        $period = $this->getCurrentPeriod();
        $checkDate = Carbon::parse($date);

        return $checkDate->between(
            Carbon::parse($period['start']),
            Carbon::parse($period['end'])
        );
    }

}
