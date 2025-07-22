<?php

namespace App\Http\Controllers\Tenant;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    // Department Index

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    public function departmentIndex(Request $request)
    {

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(10);
        $tenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->withCount(['employees as employee_count'])->get();
        $users  = $accessData['employees']->get();
        return view('tenant.departments', [
            'departments' => $departments ,
            'users' => $users,
            'branches' => $branches,
            'permission' => $permission
        ]);
    }

    public function departmentListFilter(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(10);
        $branch = $request->input('branch');
        $status = $request->input('status');
        $sortBy = $request->input('sort_by');

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $query = $accessData['departments']->withCount(['employees as employee_count'])->with(['branch']);

        if ($branch) {
            $query->where('branch_id', $branch);
        } else {
            $branchIds = $branches->pluck('id');
            $query->whereIn('branch_id', $branchIds);
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

        $departmentList = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $departmentList,
            'permission' => $permission
        ]);
    }

    public function departmentStore(Request $request)
    {
        try {

            $permission = PermissionHelper::get(10);

            if (!in_array('Create', $permission)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have the permission to create.'
                ], 403);
            }

            $validated = $request->validate([
                'department_code' => [
                    'required',
                    'string',
                    Rule::unique('departments')->where(function ($query) use ($request) {
                        return $query->where('branch_id', $request->branch_id);
                    }),
                ],
                'department_name' => [
                    'required',
                    'string',
                    Rule::unique('departments')->where(function ($query) use ($request) {
                        return $query->where('branch_id', $request->branch_id);
                    }),
                ],
                'head_of_department' => 'nullable|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
            ]);

            $department = Department::create([
                'department_code' => $request->input('department_code'),
                'department_name' => $request->input('department_name'),
                'head_of_department' => $request->input('head_of_department'),
                'branch_id' => $request->input('branch_id'),
                'status' => 'active',
            ]);


            // Fetch head of department info for description (optional)
            $head = User::with('personalInformation')->find($request->input('head_of_department'));
            $headName = $head?->personalInformation?->last_name . ', ' . $head?->personalInformation?->first_name;

            $department->load('branch');

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
                'module'      => 'Department',
                'action'      => 'Create',
                'description' => 'Created Department "' . $department->department_name . '" under Branch "' . $department->branch->name . '" with Head: ' . $headName,
                'affected_id' => $department->id,
                'old_data'    => null,
                'new_data'    => json_encode($department->toArray()),
            ]);

            // Detect if request expects JSON (API)
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Department created successfully',
                    'data' => $department,
                ], 201);
            }

            // Otherwise, assume web response
            return redirect()->back()->with('success', 'Department added successfully.');
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating department: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Something went wrong while creating the department.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    // Update Department
    public function departmentUpdate(Request $request, $id)
    {
        try {

            $permission = PermissionHelper::get(10);

            if (!in_array('Update', $permission)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have the permission to update.'
                ], 403);
            }

            $department = Department::findOrFail($id);

            // Store the old data for logging
            $oldData = $department->toArray();

            // Update department
            $department->update([
                'department_code' => $request->department_code,
                'department_name' => $request->department_name,
                'head_of_department' => $request->head_of_department,
                'branch_id' => $request->branch_id,
            ]);

            // Fetch updated data for comparison and log
            $newData = $department->fresh()->toArray();

            // Optional: Get updated head of department's name
            $head = User::with('personalInformation')->find($request->head_of_department);
            $headName = $head?->personalInformation?->last_name . ', ' . $head?->personalInformation?->first_name;

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
                'module'      => 'Department',
                'action'      => 'Update',
                'description' => 'Updated Department "' . $department->department_name . '" with Head: ' . $headName,
                'affected_id' => $department->id,
                'old_data'    => json_encode($oldData),
                'new_data'    => json_encode($newData),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Department updated successfully',
                    'data' => $department,
                ]);
            }

            return redirect()->back()->with('success', 'Department updated successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating department: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Something went wrong while updating the department.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    // Delete Department
    public function departmentDelete(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $permission = PermissionHelper::get(10);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $oldData = [
            'department' => $department->toArray(),
        ];

        $department->delete();

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
            'module'      => 'Department',
            'action'      => 'Delete',
            'description' => 'Deleted department: ' . $department->department_name . ' (ID: ' . $id . ')',
            'affected_id' => $id,
            'old_data'    => json_encode($oldData, JSON_PRETTY_PRINT),
            'new_data'    => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Department deleted successfully.',
            ], 200);
        }

        return redirect()->back()->with('success', 'Department deleted successfully.');
    }
}
