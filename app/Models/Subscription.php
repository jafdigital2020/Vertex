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
        'organization_id',
        'package_id',
        'amount_paid',
        'payment_status',
        'status',
        'subscription_start',
        'subscription_end',
        'renewed_at'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function isRenewed()
    {
        return $this->status === 'renewed';
    }
}
