<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'allowance_id',
        'type', // 'include' or 'exclude'
        'frequency', // e.g. 'monthly', 'weekly'
        'override_enabled', // boolean to indicate if override is enabled
        'override_amount', // decimal value for override amount
        'calculation_basis', // e.g. 'fixed', 'per_attended_day', 'per_attended_hour'
        'status', // e.g. 'active', 'inactive', 'complete', 'hold'
        'effective_start_date', // date when the allowance becomes effective
        'effective_end_date', // date when the allowance ends
        'notes', // optional notes
        'created_by_type', // e.g. 'user', 'global_user'
        'created_by_id', // ID of the user or global user who created the record
        'updated_by_type', // e.g. 'user', 'global_user'
        'updated_by_id', // ID of the user or global user who updated the record
    ];

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Allowance relationship
    public function allowance()
    {
        return $this->belongsTo(Allowance::class);
    }

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->morphTo();
    }

    // creator_name
    public function getCreatorNameAttribute()
    {
        if ($this->createdBy instanceof \App\Models\User) {
            return $this->createdBy->personalInformation->full_name ?? 'Unnamed User';
        }

        if ($this->createdBy instanceof \App\Models\GlobalUser) {
            return $this->createdBy->username ?? 'Unnamed Global User';
        }

        return 'Unknown Creator';
    }

    // updater_name
    public function getUpdaterNameAttribute()
    {
        if ($this->updatedBy instanceof \App\Models\User) {
            return $this->updatedBy->personalInformation->full_name ?? 'Unnamed User';
        }

        if ($this->updatedBy instanceof \App\Models\GlobalUser) {
            return $this->updatedBy->username ?? 'Unnamed Global User';
        }

        return '-';
    }
}
