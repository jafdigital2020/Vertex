<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;
use Illuminate\Validation\ValidationException;

class DesignationController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    } 
    public function designationIndex(Request $request)
    {
        $authUser = $this->authUser(); 
        $permission = PermissionHelper::get(11);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get(); 
        $designations = $accessData['designations']
            ->with('department.branch')
            ->withCount('employmentDetail')
            ->get();

        if ($request->wantsJson()) {
            // Transform designations to include only department_name and branch_name
            $designationsTransformed = $designations->map(function ($designation) {
                return [
                    'id' => $designation->id,
                    'designation_name' => $designation->designation_name,
                    'department_id' => $designation->department_id,
                    'department_name' => $designation->department ? $designation->department->department_name : null,
                    'branch_id' => $designation->department && $designation->department->branch ? $designation->department->branch->id : null,
                    'branch_name' => $designation->department && $designation->department->branch ? $designation->department->branch->name : null,
                    'job_description' => $designation->job_description,
                    'status' => $designation->status,
                    'employee_count' => $designation->employment_detail_count ?? 0,
                    'created_at' => $designation->created_at,
                    'updated_at' => $designation->updated_at,
                ];
            });

            return response()->json([
                'message' => 'Designations retrieved successfully.',
                'status' => 'success',
                'designations' => $designationsTransformed,
                'departments' => $departments,
                'branches' => $branches,
                'permission' => $permission
            ]);
        }

        return view('tenant.designations', [
            'designations'         => $designations,
            'departments'          => $departments,
            'branches'             => $branches,   
            'permission'           => $permission,
        ]);
    }
       
     public function designationFilter(Request $request)
     {   
 
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;
        $permission = PermissionHelper::get(11);
        $branch = $request->input('branch');
        $department = $request->input('department'); 
        $status = $request->input('status');
        $sortBy = $request->input('sort_by');
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $query = $accessData['designations']->with('department.branch');
        if ($branch) {
            $query->whereHas('department.branch', function ($q) use ($branch) {
                $q->where('id', $branch);
            });
        }
        if ($department) {
            $query->where('department_id', $department);
        }
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        if ($sortBy === 'ascending') {
                $query->orderBy('created_at', 'ASC');
        } elseif ($sortBy === 'descending') {
                $query->orderBy('created_at', 'DESC');
        } elseif ($sortBy === 'last_month') {
                $query->where('created_at', '>=', now()->subMonth());
        } elseif ($sortBy === 'last_7_days') {
                $query->where('created_at', '>=', now()->subDays(7));
        }

        $designation = $query->get();
      
        return response()->json([
            'status' => 'success',
            'data' => $designation,
            'permission' => $permission
        ]);
    }

    public function designation_branchFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;
        $branch_id = $request->input('branch'); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
       
        if (!empty($branch_id)) {
            $departments = $accessData['departments']->get()->where('branch_id', (int)$branch_id)->values();
        }else{
            $departments = $accessData['departments']->get()->filter(function ($department) use ($tenantId) {
                return $department->branch && $department->branch->tenant_id === $tenantId;
            })->values();
        }   
        return response()->json([
            'status' => 'success',
            'departments' => $departments , 
        ]);
    }

        public function designation_departmentFilter(Request $request)
        {
            $authUser = $this->authUser();
            $department_id = $request->input('department');
            $branch = $request->input('branch');
            $dataAccessController = new DataAccessController();
            $accessData = $dataAccessController->getAccessData($authUser); 
            
            if (!empty($department_id)) {
                $department = $accessData['departments']->where('id', $department_id)->first(); 
                $branch_id = $department?->branch_id; 
            } else {
                $branch_id = ''; 
            }

            return response()->json([
                'status' => 'success',
                'branch_id' => $branch_id, 
            ]);
        }


    // Designation API storing
    public function designationStore(Request $request)
    {     

        $permission = PermissionHelper::get(11);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ]);
        }

        $validated = $request->validate([
            'designation_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('designations')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
            'department_id' => 'required|exists:departments,id',
        ]);

        $designation = Designation::create([
            'designation_name' => $request->designation_name,
            'department_id' => $request->department_id,
            'job_description' => $request->job_description,
            'status' => 'active',
        ]);

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        // Log the action
        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Designation',
            'action'      => 'Create',
            'description' => 'Created Designation "' . $designation->designation_name . '" under Department "' . $designation->department->department_name,
            'affected_id' => $designation->id,
            'old_data'    => null,
            'new_data'    => json_encode($designation->toArray()),
        ]);

        // Detect if request expects JSON (API)
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Designation created successfully',
                'data' => $designation
            ], 201);
        }

        // Otherwise, assume web response
        return redirect()->back()->with('success', 'Designation added successfully.');
    }

    // Get Department By Branch
    public function getDepartmentsByBranch($branchId)
    {
        $departments = Department::where('branch_id', $branchId)->get();
        return response()->json($departments);
    }

    // Designation Update
    public function designationUpdate(Request $request, $id)
    {

         $permission = PermissionHelper::get(11);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ]);
        }


        try {
            // Validate required and optional fields
            $validated = $request->validate([
                'designation_name' => 'required|string|max:255',
                'department_id'    => 'required|exists:departments,id',
            ]);

            $designation = Designation::findOrFail($id);

            // Store the old data for logging
            $oldData = $designation->toArray();

            // Update only the fields sent by the user
            $designation->update([
                'designation_name' => $request->designation_name,
                'department_id'    => $request->department_id,
                'job_description'  => $request->job_description,
            ]);

            // Fetch updated data for comparison and log
            $newData = $designation->fresh()->toArray();

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'       => $userId,
                'global_user_id' => $globalUserId,
                'module'        => 'Designation',
                'action'        => 'Update',
                'description'   => 'Updated Designation "' . $designation->designation_name . '" under Department "' . $designation->department->department_name . '"',
                'affected_id'   => $designation->id,
                'old_data'      => json_encode($oldData),
                'new_data'      => json_encode($newData),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Designation updated successfully',
                    'data'    => $designation,
                ]);
            }

            return redirect()->back()->with('success', 'Designation updated successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating designation: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Something went wrong while updating the designation.',
                    'error'   => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    // Delete Designation
    public function designationDelete(Request $request, $id)
    {

        $permission = PermissionHelper::get(11);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ]);
        }


        $designation = Designation::findOrFail($id);

        $oldData = [
            'designation' => $designation->toArray(),
        ];

        $designation->delete();

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        // ✨ Log the action
        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Designation',
            'action'      => 'Delete',
            'description' => 'Deleted Designation: ' . $designation->designation_name . ' (ID: ' . $id . ')',
            'affected_id' => $id,
            'old_data'    => json_encode($oldData, JSON_PRETTY_PRINT),
            'new_data'    => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Designation deleted successfully.',
            ], 200);
        }

        return redirect()->back()->with('success', 'Designation deleted successfully.');
    }
}
