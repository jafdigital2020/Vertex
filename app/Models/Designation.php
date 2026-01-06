<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation_name',
        'department_id',
        'job_description',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Employment details relationship
    public function employmentDetail()
    {
        return $this->hasMany(EmploymentDetail::class, 'designation_id');
    }

    // Users/Employees relationship (through employment details)
    public function employees()
    {
        return $this->hasManyThrough(
            User::class,
            EmploymentDetail::class,
            'designation_id', // Foreign key on employment_details table
            'id',             // Foreign key on users table
            'id',             // Local key on designations table
            'user_id'         // Local key on employment_details table
        );
    }
}