<?php

namespace App\Models;

use App\Models\User;
use App\Models\LeaveApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_requested',
        'half_day_type',
        'file_attachment',
        'reason',
        'status',
        'current_step',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(LeaveApproval::class)
            ->latestOfMany('acted_at');
    }
}
