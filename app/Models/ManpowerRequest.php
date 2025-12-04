<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ManpowerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'position',
        'department_id',
        'designation_id',
        'vacancies',
        'salary_min',
        'salary_max',
        'employment_type',
        'justification',
        'job_description',
        'requirements',
        'skills',
        'target_start_date',
        'priority',
        'status',
        'requested_by',
        'reviewed_by',
        'approved_by',
        'job_posting_id',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'posted_at',
        'review_notes',
        'approval_notes',
        'rejection_reason',
        'is_active'
    ];

    protected $casts = [
        'requirements' => 'array',
        'skills' => 'array',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'target_start_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePendingCOOApproval($query)
    {
        return $query->where('status', 'pending_coo_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByRequester($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-warning',
            'pending_coo_approval' => 'bg-info',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'posted' => 'bg-primary',
            'filled' => 'bg-success',
            'closed' => 'bg-secondary'
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => 'bg-secondary',
            'medium' => 'bg-primary',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger'
        ];

        return $badges[$this->priority] ?? 'bg-secondary';
    }

    public function getCanEditAttribute()
    {
        return in_array($this->status, ['pending', 'rejected']);
    }

    public function getCanApproveAttribute()
    {
        return $this->status === 'pending_coo_approval';
    }

    public function getCanRejectAttribute()
    {
        return in_array($this->status, ['pending', 'pending_coo_approval']);
    }

    public function getCanPostAttribute()
    {
        return $this->status === 'approved' && !$this->job_posting_id;
    }

    // Methods
    public function submitForReview($reviewerId = null, $notes = null)
    {
        $this->update([
            'status' => 'pending_coo_approval',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
            'submitted_at' => $this->submitted_at ?: now()
        ]);
    }

    public function approve($approverId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);
    }

    public function reject($rejectedById, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedById,
            'rejection_reason' => $reason,
            'approved_at' => now()
        ]);
    }

    public function createJobPosting($createdBy)
    {
        $jobCode = 'MR-' . $this->request_number;
        
        $jobPosting = JobPosting::create([
            'job_code' => $jobCode,
            'title' => $this->position,
            'department_id' => $this->department_id,
            'designation_id' => $this->designation_id,
            'description' => $this->job_description,
            'requirements' => $this->requirements,
            'skills' => $this->skills,
            'employment_type' => $this->employment_type,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'vacancies' => $this->vacancies,
            'status' => 'open',
            'posted_date' => now(),
            'expiration_date' => $this->target_start_date ? $this->target_start_date->addDays(30) : now()->addDays(30),
            'created_by' => $createdBy,
            'is_active' => true
        ]);

        $this->update([
            'status' => 'posted',
            'job_posting_id' => $jobPosting->id,
            'posted_at' => now()
        ]);

        return $jobPosting;
    }
}