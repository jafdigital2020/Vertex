<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidatePermission;
use App\Models\Role;
use App\Models\DataAccessLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidateRoleController extends Controller
{
    /**
     * Assign role to candidate
     */
    public function assignRole(Request $request, Candidate $candidate)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:role,id',
            'data_access_id' => 'nullable|exists:data_access_level,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::find($request->role_id);

        // Update candidate role
        $candidate->update(['role_id' => $request->role_id]);

        // Create or update candidate permission
        CandidatePermission::updateOrCreate(
            ['candidate_id' => $candidate->id],
            [
                'role_id' => $request->role_id,
                'menu_ids' => $role->menu_ids,
                'module_ids' => $role->module_ids,
                'candidate_permission_ids' => $role->role_permission_ids,
                'data_access_id' => $request->data_access_id,
                'status' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully to candidate.',
            'candidate' => $candidate->fresh(['role', 'candidatePermission'])
        ]);
    }

    /**
     * Remove role from candidate
     */
    public function removeRole(Candidate $candidate)
    {
        $candidate->update(['role_id' => null]);
        $candidate->candidatePermission()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role removed successfully from candidate.'
        ]);
    }

    /**
     * Get available roles for candidates
     */
    public function getRoles()
    {
        $roles = Role::where('status', 1)->get();
        $dataAccessLevels = DataAccessLevel::all();

        return response()->json([
            'roles' => $roles,
            'data_access_levels' => $dataAccessLevels
        ]);
    }

    /**
     * Get candidate's current role and permissions
     */
    public function getCandidateRole(Candidate $candidate)
    {
        $candidate->load(['role', 'candidatePermission.dataAccessLevel']);
        
        return response()->json([
            'candidate' => $candidate,
            'role_data' => $candidate->role_data
        ]);
    }

    /**
     * Update candidate permissions
     */
    public function updatePermissions(Request $request, Candidate $candidate)
    {
        $validator = Validator::make($request->all(), [
            'menu_ids' => 'nullable|string',
            'module_ids' => 'nullable|string',
            'candidate_permission_ids' => 'nullable|string',
            'data_access_id' => 'nullable|exists:data_access_level,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidatePermission = $candidate->candidatePermission;
        
        if (!$candidatePermission) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate has no role assigned.'
            ], 400);
        }

        $candidatePermission->update([
            'menu_ids' => $request->menu_ids,
            'module_ids' => $request->module_ids,
            'candidate_permission_ids' => $request->candidate_permission_ids,
            'data_access_id' => $request->data_access_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate permissions updated successfully.',
            'candidate' => $candidate->fresh(['role', 'candidatePermission'])
        ]);
    }
}
