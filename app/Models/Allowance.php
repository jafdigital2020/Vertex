<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'allowance_name',
        'amount',
        'apply_to_all_employees', // Indicates if this allowance applies to all employees
        'is_taxable', // Indicates if the allowance is taxable
        'description', // Optional description of the allowance
        'created_by_type', // e.g. 'user', 'global_user'
        'created_by_id', // ID of the user or global user who created the allowance
        'updated_by_type', // e.g. 'user', 'global_user'
        'updated_by_id', // ID of the user or global user who updated the allowance
        'status', // e.g. 'active', 'inactive'
        'calculation_basis', // e.g. 'fixed', 'per_attended_day', 'per_attended_hour'
    ];

    // Tenant relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // User Allowances relationship
    public function userAllowances()
    {
        return $this->hasMany(UserAllowance::class, 'allowance_id');
    }

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->morphTo();
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
