<?php

namespace App\Models;

use App\Models\Geofence;
use App\Models\ShiftList;
use App\Models\ApprovalStep;
use App\Models\EmploymentDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'contact_number',
        'branch_logo',
        'branch_type', // Sub or Main (Default Sub)
        'sss_contribution_type', // system, manual, fixed, none
        'philhealth_contribution_type', // system, manual, fixed, none
        'pagibig_contribution_type', // system, manual, fixed, none
        'withholding_tax_type', // system, manual, fixed, none
        'status',
        'worked_days_per_year',
        'custom_worked_days',
        'fixed_sss_amount',
        'fixed_philhealth_amount',
        'fixed_pagibig_amount',
        'fixed_withholding_tax_amount',
        'e_signature',
        'tenant_id',
        'salary_type', // hourly_rate, monthly_fixed, daily_rate
        'basic_salary', // Nullable, used for hourly_rate and daily_rate
        'salary_computation_type', // monthly, semi-monthly, bi-weekly, weekly
        'wage_order',
        'branch_tin',
        'sss_contribution_template', // SSS contribution year template
    ];

    public function employmentDetail()
    {
        return $this->hasMany(EmploymentDetail::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function shiftLists()
    {
        return $this->hasMany(ShiftList::class);
    }

    public function geofences()
    {
        return $this->hasMany(Geofence::class);
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class);
    }

    // Policy target
    public function policyTargets()
    {
        return $this->hasMany(PolicyTarget::class, 'target_id', 'id');
    }

    public function branchSubscriptions()
    {
        return $this->hasMany(BranchSubscription::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function branchAddons()
    {
        return $this->hasMany(BranchAddon::class, 'branch_id', 'id');
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class);
    }
}
