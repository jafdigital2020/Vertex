<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Designation;
use App\Models\EmploymentDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'department_code',
        'department_name',
        'description',
        'status',
        'head_of_department',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'head_of_department');
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    public function employmentDetail()
    {
        return $this->hasMany(EmploymentDetail::class, 'department_id');
    }

    // Policy relationship
    public function policyTargets()
    {
        return $this->hasMany(PolicyTarget::class, 'target_id', 'id');
    }
}
