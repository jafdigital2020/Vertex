<?php

namespace App\Models;

use App\Models\User;
use App\Models\ApprovalStep;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class LeaveApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'approver_id',
        'step_number',
        'action',
        'comment',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public static function stepsForBranch(?int $branchId)
    {
        $raw = ApprovalStep::whereNull('branch_id')
            ->orWhere('branch_id', $branchId)
            ->orderBy('level')
            ->get();

        return $raw
            ->groupBy('level')
            ->map(
                fn($group) =>
                $group->first(fn($s) => $s->branch_id == $branchId)
                    ?? $group->first(fn($s) => is_null($s->branch_id))
            )
            ->sortBy('level')
            ->values();
    }

    public static function nextApproversFor($leave, $steps = null)
    {
        // ✅ FIXED: Get current reporting_to (not cached)
        $reportingToId = optional($leave->user->employmentDetail)->reporting_to;

        // If there's a reporting manager, return them as the next/last approver
        if ($leave->current_step === 1 && $reportingToId && $leave->status === 'pending') {
            $manager = User::with('personalInformation')->find($reportingToId);
            if ($manager && $manager->personalInformation) {
                $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                return [$managerName];
            }
            return ['Manager'];
        }

        // ✅ FIXED: Use stepsForBranch with proper branch ID
        $branchId = optional($leave->user->employmentDetail)->branch_id;
        $steps = $steps ?: static::stepsForBranch($branchId);
        $next = $steps->firstWhere('level', $leave->current_step);

        if (!$next) {
            return [];
        }

        switch ($next->approver_kind) {
            case 'user':
                $u = User::with('personalInformation')->find($next->approver_user_id);
                if ($u && $u->personalInformation) {
                    $fullName = trim("{$u->personalInformation->first_name} {$u->personalInformation->last_name}");
                    return [$fullName];
                }
                return [];

            case 'department_head':
                $headId = optional($leave->user->employmentDetail->department)->head_of_department;
                if ($headId) {
                    $h = User::with('personalInformation')->find($headId);
                    if ($h && $h->personalInformation) {
                        $fullName = trim("{$h->personalInformation->first_name} {$h->personalInformation->last_name}");
                        return [$fullName];
                    }
                }
                return [];

            case 'role':
                $users = User::with('personalInformation')
                    ->whereHas('roles', function ($q) use ($next) {
                        $q->where('name', $next->approver_value);
                    })
                    ->get();

                return $users->map(function ($u) {
                    if ($u->personalInformation) {
                        return trim("{$u->personalInformation->first_name} {$u->personalInformation->last_name}");
                    }
                    return null;
                })->filter()->values()->toArray();

            default:
                return [];
        }
    }

    public static function nextApproversForNotification($leave, $steps = null)
    {
        $steps = $steps ?: static::stepsFor($leave->user);
        $next  = $steps->firstWhere('level', $leave->current_step);

        if (! $next) {
            return [];
        }

        switch ($next->approver_kind) {
            case 'user':
                $u = User::find($next->approver_user_id);
                return $u ? [$u] : [];

            case 'department_head':
                $headId = optional($leave->user->employmentDetail->department)->head_of_department;
                if ($h = User::find($headId)) {
                    return [$h];
                }
                return [];

            case 'role':
                return User::role($next->approver_value)->get()->all();

            default:
                return [];
        }
    }

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = strtolower($value);
    }
}
