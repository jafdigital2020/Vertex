<?php

namespace App\Models;

use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\LeaveEntitlement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_entitle',
        'accrual_frequency',
        'max_carryover',
        'is_earned',
        'earned_rate',
        'earned_interval',
        'is_paid',
        'status',
        'tenant_id',
        'is_cash_convertible',
        'conversion_rate',
        'branch_id',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function leaveSetting()
    {
        return $this->hasMany(LeaveSetting::class);
    }

    public function leaveRequest()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveEntitlement()
    {
        return $this->hasMany(LeaveEntitlement::class);
    }

    public function leaveConversions()
    {
        return $this->hasMany(LeaveConversion::class);
    }
}
