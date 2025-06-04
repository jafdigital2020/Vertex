<?php

namespace App\Models;

use App\Models\UserDeduction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeductionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'calculation_method', // e.g. 'fixed', 'percentage'
        'default_amount', // Default amount for fixed deductions or percentage value
        'is_taxable', // Indicates if the deduction type is taxable
        'apply_to_all_employees', // Indicates if this deduction type applies to all employees
        'description', // Optional description of the deduction type
        'created_by_type', // e.g. 'user', 'global_user'
        'created_by_id', // ID of the user or global user who created the deduction type
        'updated_by_type', // e.g. 'user', 'global_user'
        'updated_by_id', // ID of the user or global user who updated the deduction type
    ];

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

    public function userDeductions()
    {
        return $this->hasMany(UserDeduction::class);
    }
}
