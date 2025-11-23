<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\ShiftAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftList extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id', // ID of the branch this shift belongs to
        'name', // e.g 'Morning Shift', 'Night Shift'
        'start_time',
        'end_time',
        'maximum_allowed_hours', // maximum hours allowed for this shift
        'grace_period', // grace period in minutes after the end time
        'is_flexible', // whether the shift is flexible or not
        'break_minutes', // break duration in minutes
        'notes',
        'created_by_type', // e.g 'user', 'global_user'
        'created_by_id', // ID of the user or global user who created the shift
        'updated_by_type', // e.g 'user', 'global_user'
        'updated_by_id', // ID of the user or global user who updated the shift
        'allowed_minutes_before_clock_in', // new column for allowed minutes before clock-in
        'allow_extra_hours', // new column to indicate if extra hours are allowed
    ];

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->morphTo();
    }

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class, 'shift_id');
    }

    public function getCreatorNameAttribute()
    {
        if ($this->createdBy instanceof \App\Models\User) {
            return $this->createdBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->createdBy instanceof \App\Models\GlobalUser) {
            return $this->createdBy->username ?? 'Unnamed Global User';
        }

        return 'Unknown Creator';
    }

    public function getUpdaterNameAttribute()
    {
        if ($this->updatedBy instanceof \App\Models\User) {
            return $this->updatedBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->updatedBy instanceof \App\Models\GlobalUser) {
            return $this->updatedBy->username ?? 'Unnamed Global User';
        }

        return 'N/A';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
