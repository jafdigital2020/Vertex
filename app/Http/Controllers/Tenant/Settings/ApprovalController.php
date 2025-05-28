<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Models\User;
use App\Models\Branch;
use App\Models\ApprovalStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    public function approvalIndex(Request $request)
    {
        $branches = Branch::where('status', 'active')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Leave type settings',
                'branches' => $branches,
            ]);
        }

        return view('tenant.settings.approvalsteps', [
            'branches' => $branches,
        ]);
    }

    // Get Users
    public function getUsers(Request $request)
    {
        $bid = $request->query('branch_id');

        // 1) Fetch matching users (no DB orderBy)
        $raw = User::whereHas('employmentDetail', function ($q) use ($bid) {
            if ($bid) {
                $q->where('branch_id', $bid);
            }
            $q->where('status', 'active');
        })
            ->with('personalInformation')
            ->get();

        // 2) Map into id/name and sort by name
        $users = $raw->map(function ($u) {
            $pi = $u->personalInformation;
            return [
                'id'   => $u->id,
                'name' => trim("{$pi->first_name} {$pi->last_name}"),
            ];
        })
            ->sortBy('name')
            ->values();

        return response()->json(['users' => $users]);
    }

    // Get Approval Steps
    public function getSteps(Request $request)
    {
        $bid = $request->query('branch_id');

        Log::debug('getSteps called', ['branch_id' => $bid]);

        $query = ApprovalStep::with('approverUser.personalInformation');

        if ($bid) {
            // 1) Branch-specific first, then global
            $query->where(function ($q) use ($bid) {
                $q->where('branch_id', $bid)
                    ->orWhereNull('branch_id');
            });
        } else {
            // 2) Global view only
            $query->whereNull('branch_id');
        }

        $steps = $query
            ->orderByRaw('branch_id IS NULL, branch_id DESC') // non-null first, then null
            ->orderBy('level')
            ->get()
            ->map(function ($step) {
                $user = null;
                if ($step->approver_kind === 'user' && $step->approverUser) {
                    $pi = $step->approverUser->personalInformation;
                    $user = [
                        'id'   => $step->approverUser->id,
                        'name' => trim("{$pi->first_name} {$pi->last_name}"),
                    ];
                }
                return [
                    'level'         => $step->level,
                    'approver_kind' => $step->approver_kind,
                    'approver_user' => $user,
                    'is_global'     => is_null($step->branch_id),
                ];
            });

        return response()->json([
            'steps' => $steps
        ], 200);
    }


    // Saving Approval Steps
    public function approvalStepStore(Request $request)
    {
        // 1) Validation: branch_id may be null for global
        $validator = Validator::make($request->all(), [
            'branch_id'                => 'nullable|exists:branches,id',
            'steps'                    => 'required|array|min:1',
            'steps.*.level'            => 'required|integer|min:1',
            'steps.*.approver_kind'    => 'required|in:department_head,user',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data     = $validator->validated();
        // treat empty string as NULL
        $branchId = $data['branch_id'] ?? null;
        $levels   = collect($data['steps'])->pluck('level')->all();

        // 2) enforce that user steps have a user selected
        foreach ($data['steps'] as $step) {
            if ($step['approver_kind'] === 'user' && empty($step['approver_user_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Level {$step['level']} requires a user.",
                ], 422);
            }
        }

        // 3) Upsert in transaction (handles create, update, delete)
        DB::transaction(function () use ($branchId, $data, $levels) {
            // delete any removed levels for this branch (including global if branchId==null)
            ApprovalStep::where('branch_id', $branchId)
                ->whereNotIn('level', $levels)
                ->delete();

            // upsert each submitted step
            foreach ($data['steps'] as $step) {
                ApprovalStep::updateOrCreate(
                    [
                        'branch_id' => $branchId,
                        'level'     => $step['level'],
                    ],
                    [
                        'approver_kind'    => $step['approver_kind'],
                        'approver_user_id' => $step['approver_user_id'],
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Approval steps saved successfully.',
            'data'    => [
                'branch_id' => $branchId,
                'steps'     => $data['steps'],
            ],
        ], 200);
    }
}
