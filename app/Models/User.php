<?php

namespace App\Models;

use App\Models\Bank;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\GeofenceUser;
use App\Models\LeaveRequest;
use App\Models\SalaryDetail;
use App\Models\SalaryRecord;
use App\Models\LeaveApproval;
use App\Models\ShiftAssignment;
use App\Models\EmploymentDetail;
use App\Models\HolidayException;
use App\Models\LeaveEntitlement;
use Laravel\Sanctum\HasApiTokens;
use App\Models\EmploymentGovernmentId;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id',
        'organization_code', //Temporary For Development
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Department Relationship (Head)
    public function headOfDepartment()
    {
        return $this->hasOne(Department::class, 'head_of_department');
    }

    // Employee Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Employee Designation(Position)
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    // Employment Details Relationship
    public function employmentDetail()
    {
        return $this->hasOne(EmploymentDetail::class);
    }

    // Subordinates Relationship
    public function teamMember()
    {
        return $this->hasMany(EmploymentDetail::class, 'reporting_to');
    }

    // Employee Personal Information
    public function personalInformation()
    {
        return $this->hasOne(EmploymentPersonalInformation::class, 'user_id');
    }

    // Employee Government ID's
    public function governmentDetail()
    {
        return $this->hasOne(EmploymentGovernmentId::class);
    }

    // Spatie Role Accessor
    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    // Branch Accessor
    public function branch()
    {
        return $this->hasOneThrough(Branch::class, EmploymentDetail::class, 'user_id', 'id', 'id', 'branch_id');
    }

    //Logs Accesor
    public function logs()
    {
        return $this->hasMany(UserLog::class);
    }

    //Government ID Accesor
    public function governmentId()
    {
        return $this->hasOne(EmploymentGovernmentId::class, 'user_id');
    }

    // Bank (Employee) Accessor
    public function employeeBank()
    {
        return $this->hasOne(EmployeeBankDetail::class);
    }

    // Family Information Accessor
    public function family()
    {
        return $this->hasMany(EmployeeFamilyInformation::class, 'user_id');
    }

    // Employee Education Accessor
    public function education()
    {
        return $this->hasMany(EmployeeEducationDetails::class, 'user_id');
    }

    // Employee Experience Accessor
    public function experience()
    {
        return $this->hasMany(EmployeeExperience::class, 'user_id');
    }

    // Employee Emergency Contact Accessor
    public function emergency()
    {
        return $this->hasOne(EmployeeEmergencyContact::class, 'user_id');
    }

    // Salary Record Accessor(For History)
    public function salaryRecord()
    {
        return $this->hasMany(SalaryRecord::class, 'user_id');
    }

    // Get Active Salary
    public function activeSalary()
    {
        return $this->hasOne(SalaryRecord::class)->where('is_active', true);
    }

    // Salary Details Accessor
    public function salaryDetail()
    {
        return $this->hasOne(SalaryDetail::class, 'user_id');
    }

    // Shift Assignment Accessor
    public function shiftAssignment()
    {
        return $this->hasMany(ShiftAssignment::class, 'user_id');
    }

    // Geofence Accessor
    public function geofenceUser()
    {
        return $this->hasMany(GeofenceUser::class, 'user_id');
    }

    // Holiday Exception Accessor
    public function holidayException()
    {
        return $this->hasMany(HolidayException::class, 'user_id');
    }

    // Approval Steps Accessor
    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class, 'approver_user_id');
    }

    // Leave Entitlement Accessor
    public function leaveEntitlement()
    {
        return $this->hasMany(LeaveEntitlement::class, 'user_id');
    }

    // Leave Request Accessor
    public function leaveRequest()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    // Leave Approval Accessor
    public function leaveApproval()
    {
        return $this->hasMany(LeaveApproval::class, 'approver_id');
    }
}
