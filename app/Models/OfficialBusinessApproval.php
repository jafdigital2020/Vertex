<?php

namespace App\Models;

use App\Models\User;
use App\Models\OfficialBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficialBusinessApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_business_id',
        'approver_id',
        'step_number',
        'action',
        'comment',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function officialBusiness()
    {
        return $this->belongsTo(OfficialBusiness::class, 'ob_id');
    }

    public function obApprover()
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

    public static function nextApproversFor($ob, $steps = null)
    {
        // Reporting to
        $reportingToId = optional($ob->user->employmentDetail)->reporting_to;

        // If there's reporting_to, return them as next/last approver
        if ($ob->current_step === 1 && $reportingToId && $ob->status === 'pending') {
            $manager = User::with('personalInformation')->find($reportingToId);
            if ($manager && $manager->personalInformation) {
                $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                return [$managerName];
            }
            return ['Manager'];
        }

        $branchId = optional($ob->user->employmentDetail)->branch_id;
        $steps = $steps ?: static::stepsForBranch($branchId);
        $next  = $steps->firstWhere('level', $ob->current_step);

        if (! $next) {
            return [];
        }

        switch ($next->approver_kind) {
            case 'user':
                $u = User::find($next->approver_user_id);
                return $u
                    ? [optional($u->personalInformation)->full_name]
                    : [];

            case 'department_head':
                $headId = optional($ob->user->employmentDetail->department)->head_of_department;
                if ($h = User::find($headId)) {
                    return [optional($h->personalInformation)->full_name];
                }
                return [];

            case 'role':
                return User::role($next->approver_value)
                    ->get()
                    ->map(fn($u) => optional($u->personalInformation)->full_name)
                    ->filter()
                    ->all();

            default:
                return [];
        }
    }

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = strtolower($value);
    }
}
