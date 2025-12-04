<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_code',
        'title',
        'department_id',
        'designation_id',
        'location',
        'description',
        'requirements',
        'skills',
        'employment_type',
        'salary_min',
        'salary_max',
        'vacancies',
        'status',
        'posted_date',
        'expiration_date',
        'created_by',
        'assigned_recruiter',
        'is_active'
    ];

    protected $casts = [
        'requirements' => 'array',
        'skills' => 'array',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'posted_date' => 'date',
        'expiration_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recruiter()
    {
        return $this->belongsTo(User::class, 'assigned_recruiter');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'job_applications')
                    ->withPivot(['status', 'applied_at'])
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
                     ->where('expiration_date', '>=', Carbon::today());
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByRecruiter($query, $recruiterId)
    {
        return $query->where('assigned_recruiter', $recruiterId);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiration_date && $this->expiration_date < Carbon::today();
    }

    public function getApplicationsCountAttribute()
    {
        return $this->applications()->count();
    }

    public function getDaysToExpiryAttribute()
    {
        if (!$this->expiration_date) {
            return null;
        }
        
        return Carbon::today()->diffInDays($this->expiration_date, false);
    }
}