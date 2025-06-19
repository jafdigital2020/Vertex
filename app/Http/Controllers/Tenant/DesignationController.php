<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DesignationController extends Controller
{
    //Designation Index
    public function designationIndex(Request $request)
    {
        $departments = Department::all();
        $branches = Branch::all();

        // Get default 'main' branch
        // $mainBranch = Branch::where('branch_type', 'main')->first();

        $branchId = $request->has('branch_id') ? $request->input('branch_id') : null;
        $departmentId = $request->input('department_id');
        $status = $request->input('status');
        $sort = $request->input('sort');

        $designations = Designation::query()
            ->withCount(['employmentDetail as active_employees_count' => function ($query) {
                $query->where('status', '1'); // Only count active employees
            }]);

        if ($branchId) {
            $designations->whereHas('department', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        if ($departmentId) {
            $designations->where('department_id', $departmentId);
        }

        if ($status) {
            $designations->where('status', $status);
        }

        if ($sort === 'asc') {
            $designations->orderBy('created_at', 'asc');
        } elseif ($sort === 'desc') {
            $designations->orderBy('created_at', 'desc');
        } elseif ($sort === 'last_month') {
            $designations->where('created_at', '>=', now()->subMonth());
        } elseif ($sort === 'last_7_days') {
            $designations->where('created_at', '>=', now()->subDays(7));
        }

        return view('tenant.designations', [
            'designations' => $designations->get(),
            'departments' => $departments,
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'selectedDepartmentId' => $departmentId,
            'selectedStatus' => $status,
            'selectedSort' => $sort
        ]);
    }

    // Designation API storing
    public function designationStore(Request $request)
    {
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
        try {
            $designation = Designation::findOrFail($id);

            // Store the old data for logging
            $oldData = $designation->toArray();

            // Update department
            $designation->update([
                'designation_name' => $request->designation_name,
                'department_id' => $request->department_id,
                'job_description' => $request->job_description,
            ]);

            // Fetch updated data for comparison and log
            $newData = $designation->fresh()->toArray();

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
                'action'      => 'Update',
                'description' => 'Updated Designation "' . $designation->designation_name . '" under Department "' . $designation->department->department_name,
                'affected_id' => $designation->id,
                'old_data'    => json_encode($oldData),
                'new_data'    => json_encode($newData),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Designation updated successfully',
                    'data' => $designation,
                ]);
            }

            return redirect()->back()->with('success', 'Designation updated successfully!');
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

    // Delete Designation
    public function designationDelete(Request $request, $id)
    {
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
