<?php

namespace App\Models;

use App\Models\User;
use App\Models\Overtime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OvertimeApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_id',
        'approver_id',
        'status',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function overtime()
    {
        return $this->belongsTo(Overtime::class);
    }

    public function otApprover()
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

    public static function nextApproversFor($overtime, $steps = null)
    {
        // Reporting to logic
        $reportingToId = optional($overtime->user->employmentDetail)->reporting_to;

        // If there's a reporting to set, override the department head step
        if ($overtime->current_step === 1 && $reportingToId && $overtime->status === 'pending') {
            $manager = User::with('personalInformation')->find($reportingToId);
            if ($manager && $manager->personalInformation) {
                $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                return [$managerName];
            }
            return ['Manager'];
        }

        $branchId = optional($overtime->user->employmentDetail)->branch_id;
        $steps = $steps ?: static::stepsForBranch($branchId);
        $next  = $steps->firstWhere('level', $overtime->current_step);

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
                $headId = optional($overtime->user->employmentDetail->department)->head_of_department;
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
