<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RecruitmentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'branch_id',
        'level',
        'status',
        'approver_id',
        'approved_at',
        'comments',
        'rejection_reason',
        'updated_by_type',
        'updated_by_id'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the approvable model (ManpowerRequest, JobPosting, JobOffer)
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get who updated this record (polymorphic)
     */
    public function updatedBy(): MorphTo
    {
        return $this->morphTo('updated_by');
    }

    /**
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific approval level
     */
    public function scopeForLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if this is the next required approval
     */
    public function isNextApprovalRequired(): bool
    {
        // Check if all previous levels are approved
        $previousLevels = static::where('approvable_type', $this->approvable_type)
            ->where('approvable_id', $this->approvable_id)
            ->where('branch_id', $this->branch_id)
            ->where('level', '<', $this->level)
            ->get();

        return $previousLevels->every(fn($approval) => $approval->status === 'approved');
    }

    /**
     * Auto-assign approver based on approval steps configuration
     */
    public function assignApprover(): void
    {
        $approvalStep = ApprovalStep::where('branch_id', $this->branch_id)
            ->where('level', $this->level)
            ->first();

        if ($approvalStep) {
            if ($approvalStep->approver_kind === 'user' && $approvalStep->approver_user_id) {
                $this->approver_id = $approvalStep->approver_user_id;
            } elseif ($approvalStep->approver_kind === 'department_head' && $this->approvable) {
                // Auto-assign department head based on the approvable item's department
                $departmentId = $this->approvable->department_id ?? null;
                if ($departmentId) {
                    $department = Department::find($departmentId);
                    if ($department && $department->head_of_department) {
                        $this->approver_id = $department->head_of_department;
                    }
                }
            }
            $this->save();
        }
    }
}