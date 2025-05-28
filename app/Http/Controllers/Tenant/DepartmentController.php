<?php

namespace App\Http\Controllers\Tenant;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    // Department Index
    public function departmentIndex(Request $request)
    {
        $branches = Branch::all();
        $users = User::where('status', 'active')->get();

        // Get default 'main' branch
        // $mainBranch = Branch::where('branch_type', 'main')->first();

        $branchId = $request->has('branch_id') ? $request->input('branch_id') : null;
        $status = $request->input('status');
        $sort = $request->input('sort');

        $departments = Department::query()
            ->withCount([
                'employees as active_employees_count' => function ($query) {
                    $query->where('status', 'active');
                }
            ]);

        if ($branchId) {
            $departments->where('branch_id', $branchId);
        }

        if ($status) {
            $departments->where('status', $status);
        }

        if ($sort === 'asc') {
            $departments->orderBy('created_at', 'asc');
        } elseif ($sort === 'desc') {
            $departments->orderBy('created_at', 'desc');
        } elseif ($sort === 'last_month') {
            $departments->where('created_at', '>=', now()->subMonth());
        } elseif ($sort === 'last_7_days') {
            $departments->where('created_at', '>=', now()->subDays(7));
        }

        return view('tenant.departments', [
            'departments' => $departments->get(),
            'users' => $users,
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'selectedStatus' => $status,
            'selectedSort' => $sort
        ]);
    }

    // Department API storing
    public function departmentStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'department_code' => 'required|string|unique:departments,department_code',
                'department_name' => 'required|string|unique:departments,department_name',
                'head_of_department' => 'required|exists:users,id',
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
