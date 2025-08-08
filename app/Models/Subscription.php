<?php

namespace App\Models;

use App\Models\Package;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan',
        'amount_paid',
        'payment_status',
        'status',
        'subscription_start',
        'subscription_end',
        'trial_start',
        'trial_end',
        'renewed_at'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
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
}
