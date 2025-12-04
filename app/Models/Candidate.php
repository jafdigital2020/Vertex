<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Candidate extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'candidate_code',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'nationality',
        'marital_status',
        'linkedin_profile',
        'summary',
        'skills',
        'current_position',
        'current_company',
        'current_salary',
        'expected_salary',
        'resume_path',
        'photo_path',
        'availability',
        'notes',
        'status',
        'source_id',
        'source_type',
        'is_active'
    ];

    protected $casts = [
        'skills' => 'array',
        'date_of_birth' => 'date',
        'current_salary' => 'decimal:2',
        'expected_salary' => 'decimal:2',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['role_data'];

    public function education()
    {
        return $this->hasMany(CandidateEducation::class);
    }

    public function experience()
    {
        return $this->hasMany(CandidateExperience::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'job_applications')
                    ->withPivot(['status', 'applied_at'])
                    ->withTimestamps();
    }

    public function sourceJobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'source_id');
    }

    public function interviews()
    {
        return $this->hasManyThrough(Interview::class, JobApplication::class);
    }

    public function offers()
    {
        return $this->hasManyThrough(JobOffer::class, JobApplication::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithSkill($query, $skill)
    {
        return $query->whereJsonContains('skills', $skill);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getYearsOfExperienceAttribute()
    {
        return $this->experience->sum(function ($exp) {
            $start = $exp->start_date;
            $end = $exp->end_date ?? now();
            return $start->diffInYears($end);
        });
    }

    public function getLatestApplicationAttribute()
    {
        return $this->applications()->latest()->first();
    }

    public function getResumeUrlAttribute()
    {
        return $this->resume_path ? asset('storage/' . $this->resume_path) : null;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    // Role relationship
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Candidate permission relationship
    public function candidatePermission()
    {
        return $this->hasOne(CandidatePermission::class, 'candidate_id');
    }

    // Get role data attribute (similar to User model)
    public function getRoleDataAttribute()
    {
        $candidatePermission = $this->candidatePermission;
        
        if (!$candidatePermission) {
            return null;
        }

        return [
            'role_id' => $candidatePermission->role_id,
            'menu_ids' => $candidatePermission->menu_ids,
            'module_ids' => $candidatePermission->module_ids,
            'candidate_permission_ids' => $candidatePermission->candidate_permission_ids,
            'data_access_id' => $candidatePermission->data_access_id,
        ];
    }

    // Check if candidate has permission
    public function hasPermission($permissionId)
    {
        $roleData = $this->role_data;
        
        if (!$roleData || !$roleData['candidate_permission_ids']) {
            return false;
        }

        $permissions = explode(',', $roleData['candidate_permission_ids']);
        return in_array($permissionId, $permissions);
    }

    // Check if candidate has module access
    public function hasModuleAccess($moduleId)
    {
        $roleData = $this->role_data;
        
        if (!$roleData || !$roleData['module_ids']) {
            return false;
        }

        $modules = explode(',', $roleData['module_ids']);
        return in_array($moduleId, $modules);
    }
}