<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchSubscription extends Model
{
     use HasFactory;
    //
    protected $fillable = [
        'branch_id', 'plan', 'plan_details', 'amount_paid', 'currency',
        'payment_status', 'subscription_start', 'subscription_end',
        'trial_start', 'trial_end', 'status', 'renewed_at', 'cancelled_at',
        'payment_gateway', 'transaction_reference', 'notes', 'mobile_number',
        'total_employee',
        'tenant_id',
        'billing_period', 
        'is_trial',
         'employee_credits',
        'next_renewal_date'
    ];

    protected $casts = [
        'plan_details' => 'array',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'trial_start' => 'date',
        'trial_end' => 'date',
        'renewed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_trial' => 'boolean'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
