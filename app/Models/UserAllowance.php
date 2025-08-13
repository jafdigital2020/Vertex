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
        'type', // e.g. 'included', 'excluded'
        'frequency',
        'override_enabled', // Indicates if the user has an override for this allowance
        'override_amount', // Amount of the override if applicable
        'calculation_basis', // e.g. 'fixed', 'per_attended_day', 'per_attended_hour
        'status', // e.g. 'active', 'inactive', 'complete', 'hold'
        'effective_start_date', // Start date for the allowance to be effective
        'effective_end_date', // End date for the allowance to be effective (nullable)
        'notes', // Optional notes for the user allowance
        'created_by_id', // ID of the user or global user who created the allowance
        'created_by_type', // e.g. 'user', 'global_user'
        'updated_by_id', // ID of the user or global user who updated the allowance
        'updated_by_type', // e.g. 'user', 'global_user'
    ];

    protected $casts = [
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allowance()
    {
        return $this->belongsTo(Allowance::class);
    }

    public function createdBy()
    {
        return $this->morphTo('created_by');
    }

    public function updatedBy()
    {
        return $this->morphTo('updated_by');
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
}
