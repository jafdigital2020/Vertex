<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmploymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'designation_id',
        'employment_type',
        'employment_status',
        'date_hired',
        'end_of_contract',
        'reporting_to',
        'status',
        'branch_id',
    ];

    // User Table Relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Department Relationship
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Desgination Relationship
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    // User Relationship (Manager or reporting to)
    public function manager()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    // Branch Relationship (Sub Companies)
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
