<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAttendanceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_attendance_id',
        'approver_id',
        'step_number',
        'action', // approved, rejected, cancelled
        'comment',
        'status',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function requestAttendance()
    {
        return $this->belongsTo(RequestAttendance::class, 'request_attendance_id');
    }

    public function attendanceApprover()
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

    public static function nextApproversFor($attendance, $steps = null)
    {
        // Reporting to
        $reportingToId = optional($attendance->user->employmentDetail)->reporting_to;

        if ($attendance->current_step === 1 && $reportingToId && $attendance->status === 'pending') {
            $manager = User::with('personalInformation')->find($reportingToId);
            if ($manager && $manager->personalInformation) {
                $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                return [$managerName];
            }
            return ['Manager'];
        }

        $branchId = optional($attendance->user->employmentDetail)->branch_id;
        $steps = $steps ?: static::stepsForBranch($branchId);
        $next  = $steps->firstWhere('level', $attendance->current_step);

        if (! $next) {
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
                $headId = optional($attendance->user->employmentDetail->department)->head_of_department;
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

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = strtolower($value);
    }
}
