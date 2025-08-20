<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SalaryBondController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Salary Bond Index
    public function salaryBondIndex(Request $request, $userId)
    {
        $authUser = $this->authUser();
        $user = User::with('salaryBonds')->findOrFail($userId);

        if (request()->expectsJson()) {
            return response()->json([
                'user' => $user->only(['id', 'name']),
            ]);
        }

        return view('tenant.employee.salaries.salarybond', compact('user'));
    }

    // Add Salary Bond
    public function addSalaryBond(Request $request, $userId)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'payable_in' => 'required|integer|min:1',
                'payable_amount' => 'required|numeric|min:0',
                'date_issued' => 'required|date',
                'remarks' => 'nullable|string|max:255',
            ]);

            // Find the user
            $user = User::findOrFail($userId);

            // Create the salary bond
            $salaryBond = $user->salaryBonds()->create([
                'amount' => $validated['amount'],
                'payable_in' => $validated['payable_in'],
                'payable_amount' => $validated['payable_amount'],
                'date_issued' => $validated['date_issued'],
                'remarks' => $validated['remarks'] ?? null,
                'remaining_amount' => $validated['amount'],
            ]);

            // User Logs
            $empId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'Salary Bond',
                'action' => 'Create',
                'description' => 'Created salary bond for user ID: ' . $userId,
                'affected_id' => $salaryBond->id,
                'old_data' => null,
                'new_data' => json_encode($salaryBond->toArray()),
            ]);

            return response()->json([
                'message' => 'Salary bond created successfully.',
                'salaryBond' => $salaryBond,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Please check the information you entered.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found. Please select a valid employee.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while creating the salary bond. Please try again.',
            ], 500);
        }
    }

    // Edit Salary Bond
    public function editSalaryBond(Request $request, $userId, $salaryBondId)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'payable_in' => 'required|integer|min:1',
                'payable_amount' => 'required|numeric|min:0',
                'date_issued' => 'required|date',
                'remarks' => 'nullable|string|max:255',
            ]);

            // Find the user and salary bond
            $user = User::findOrFail($userId);
            $salaryBond = $user->salaryBonds()->findOrFail($salaryBondId);

            // Update the salary bond
            $salaryBond->update($validated);

            return response()->json([
                'message' => 'Salary bond updated successfully.',
                'salaryBond' => $salaryBond,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Please check the information you entered.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee or salary bond not found. Please select a valid employee and salary bond.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while updating the salary bond. Please try again.',
            ], 500);
        }
    }

    // Delete Salary Bond
    public function deleteSalaryBond(Request $request, $userId, $salaryBondId)
    {
        try {
            // Find the user and salary bond
            $user = User::findOrFail($userId);
            $salaryBond = $user->salaryBonds()->findOrFail($salaryBondId);

            // Delete the salary bond
            $salaryBond->delete();

            // User Logs
            $empId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'Salary Bond',
                'action' => 'Delete',
                'description' => 'Deleted salary bond for user ID: ' . $userId,
                'affected_id' => $salaryBond->id,
                'old_data' => json_encode($salaryBond->toArray()),
                'new_data' => null,
            ]);

            return response()->json([
                'message' => 'Salary bond deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee or salary bond not found. Please select a valid employee and salary bond.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while deleting the salary bond. Please try again.',
            ], 500);
        }
    }
}
