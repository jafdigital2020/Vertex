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
}
