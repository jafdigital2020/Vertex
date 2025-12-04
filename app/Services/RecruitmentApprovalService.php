<?php

namespace App\Services;

use App\Models\RecruitmentApproval;
use App\Models\ApprovalStep;
use App\Models\ManpowerRequest;
use App\Models\JobPosting;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Auth;

class RecruitmentApprovalService
{
    /**
     * Initialize approval workflow for any recruitment item
     */
    public function initializeApprovalWorkflow($model, int $branchId): void
    {
        $approvalSteps = ApprovalStep::where('branch_id', $branchId)
            ->orderBy('level')
            ->get();

        foreach ($approvalSteps as $step) {
            $approval = RecruitmentApproval::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'branch_id' => $branchId,
                'level' => $step->level,
                'status' => 'pending'
            ]);

            $approval->assignApprover();
        }

        // Update the model status to reflect approval is needed
        $this->updateModelStatus($model, 'pending_approval');
    }

    /**
     * Process approval for a specific level
     */
    public function processApproval($model, int $level, int $approverId, string $action, ?string $comments = null): bool
    {
        $approval = RecruitmentApproval::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->where('level', $level)
            ->first();

        if (!$approval || $approval->status !== 'pending') {
            return false;
        }

        // Verify this is the next required approval
        if (!$approval->isNextApprovalRequired()) {
            return false;
        }

        $approval->update([
            'status' => $action, // 'approved' or 'rejected'
            'approver_id' => $approverId,
            'approved_at' => now(),
            'comments' => $comments,
            'rejection_reason' => $action === 'rejected' ? $comments : null,
            'updated_by_type' => get_class(Auth::user()),
            'updated_by_id' => Auth::id()
        ]);

        if ($action === 'rejected') {
            // Mark all subsequent approvals as cancelled
            RecruitmentApproval::where('approvable_type', get_class($model))
                ->where('approvable_id', $model->id)
                ->where('level', '>', $level)
                ->update(['status' => 'cancelled']);

            $this->updateModelStatus($model, 'rejected');
            return true;
        }

        // Check if all approvals are complete
        $pendingApprovals = RecruitmentApproval::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingApprovals === 0) {
            $this->updateModelStatus($model, 'approved');
        }

        return true;
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(int $userId)
    {
        return RecruitmentApproval::with(['approvable', 'branch'])
            ->where('approver_id', $userId)
            ->where('status', 'pending')
            ->get()
            ->filter(function ($approval) {
                return $approval->isNextApprovalRequired();
            });
    }

    /**
     * Get approval history for a model
     */
    public function getApprovalHistory($model)
    {
        return RecruitmentApproval::with(['approver', 'updatedBy'])
            ->where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->orderBy('level')
            ->get();
    }

    /**
     * Check if user can approve a specific item
     */
    public function canUserApprove($model, int $userId): bool
    {
        $nextApproval = RecruitmentApproval::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->where('approver_id', $userId)
            ->where('status', 'pending')
            ->first();

        return $nextApproval && $nextApproval->isNextApprovalRequired();
    }

    /**
     * Update model status based on approval workflow
     */
    private function updateModelStatus($model, string $status): void
    {
        if ($model instanceof ManpowerRequest) {
            $statusMap = [
                'pending_approval' => 'pending',
                'approved' => 'approved', 
                'rejected' => 'rejected'
            ];
            $model->update(['status' => $statusMap[$status] ?? $status]);
        } elseif ($model instanceof JobPosting) {
            $statusMap = [
                'pending_approval' => 'draft',
                'approved' => 'open',
                'rejected' => 'cancelled'
            ];
            $model->update(['status' => $statusMap[$status] ?? $status]);
        } elseif ($model instanceof JobOffer) {
            $statusMap = [
                'pending_approval' => 'draft',
                'approved' => 'sent',
                'rejected' => 'withdrawn'
            ];
            $model->update(['status' => $statusMap[$status] ?? $status]);
        }
    }

    /**
     * Get approval progress for a model
     */
    public function getApprovalProgress($model): array
    {
        $approvals = $this->getApprovalHistory($model);
        
        return [
            'total_levels' => $approvals->count(),
            'completed_levels' => $approvals->where('status', 'approved')->count(),
            'current_level' => $approvals->where('status', 'pending')->min('level'),
            'is_rejected' => $approvals->where('status', 'rejected')->isNotEmpty(),
            'is_complete' => $approvals->every(fn($a) => $a->status === 'approved'),
            'progress_percentage' => $approvals->count() > 0 
                ? round(($approvals->where('status', 'approved')->count() / $approvals->count()) * 100, 2)
                : 0
        ];
    }

    /**
     * Check if user has permission to view recruitment data based on data access level
     */
    public function hasDataAccess($user, $model): bool
    {
        $userPermission = $user->userPermission;
        if (!$userPermission) {
            return false;
        }

        $dataAccessLevel = $userPermission->dataAccessLevel;
        if (!$dataAccessLevel) {
            return true; // No restriction
        }

        switch ($dataAccessLevel->level_name) {
            case 'Organization':
                return true; // Access to all
            
            case 'Branch':
                return $user->branch_id === $model->branch_id;
            
            case 'Department':
                return $user->department_id === $model->department_id;
            
            case 'Personal':
                return $model->created_by === $user->id || 
                       $model->requested_by === $user->id;
            
            default:
                return false;
        }
    }
}