<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\PayrollBatchUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PayrollBatchSettings;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class PayrollBatchController extends Controller
{
      public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    public function payrollBatchUsersFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(50);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation'); 
        $batch = $request->input('batch');
        $query  = $accessData['employees'];
        if ($batch === '-1') { 
            $query->whereDoesntHave('payrollBatchUsers');
        } elseif (!empty($batch)) { 
            $query->whereHas('payrollBatchUsers', function ($q) use ($batch) {
                $q->where('pbsettings_id', $batch);
            });
        }
        if ($branch) {
            $query->whereHas('employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        } 
        $users = $query->get(); 
        $html = view('tenant.payroll.payrollbatch.payrollbatchusers_filter', compact('users', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function payrollBatchUsersIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(50);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $users = $accessData['employees']->get();
        $payrollBatchSettings = $accessData['payrollBatchSettings']->get();
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get(); 
 
        return view('tenant.payroll.payrollbatch.payrollbatchusers', [ 
            'permission'=> $permission, 
            'users' => $users,
            'branches' => $branches,
            'departments'=> $departments, 
            'designations'=> $designations, 
            'payrollBatchSettings' => $payrollBatchSettings
        ]);
    }

    public function payrollBatchUsersUpdate(Request $request)
    {   

        $permission = PermissionHelper::get(50);
        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'batch_ids' => 'nullable|array'
        ]);

        $batchIds = $request->input('batch_ids', []);

        DB::transaction(function() use ($request, $batchIds) {
            PayrollBatchUsers::where('user_id', $request->input('user_id'))->delete();

            if (!empty($batchIds)) {
                $batchUsers = collect($batchIds)->map(function ($batchId) use ($request) {
                    return [
                        'user_id' => $request->input('user_id'),
                        'pbsettings_id' => $batchId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                })->values()->toArray();

                PayrollBatchUsers::insert($batchUsers);
            }
        });

        return response()->json(['success' => true, 'message' => 'Payroll batches updated.']);
    }
    public function fetchDepartments(Request $request)
    {   
        $authUser = $this->authUser(); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $departments = $accessData['departments']->whereIn('branch_id', $request->input('branches'))
            ->pluck('department_name', 'id');
        return response()->json(['departments' => $departments]);
    }

    public function fetchDesignations(Request $request)
    {    
        $authUser = $this->authUser(); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $designations = $accessData['designations']->whereIn('department_id', $request->input('departments'))
            ->pluck('designation_name', 'id');
        return response()->json(['designations' => $designations]);
    }

    public function fetchEmployees(Request $request)
    {  
        $authUser = $this->authUser(); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $employees = $accessData['employees']
            ->whereHas('employmentDetail', function ($query) use ($request) {
                $query->whereIn('designation_id', $request->input('designations', []));
            })
            ->with('personalInformation')
            ->get()
            ->mapWithKeys(function ($user) {
                $info = $user->personalInformation;
                if (!$info) return [];

                $middle = $info->middle_name ? " {$info->middle_name}" : '';
                $fullName = trim("{$info->first_name}{$middle} {$info->last_name}");

                return [$user->id => $fullName];
            });

      

        return response()->json(['employees' => $employees]);
    }
 
 
    public function payrollBatchBulkAssign(Request $request)
    {
        DB::beginTransaction();

        try {
            $userIds = [];

            if ($request->filled('employees')) { 
                $userIds = $request->input('employees', []);
            } else { 
                $query = User::query();

                $query->whereHas('employmentDetail', function ($q) use ($request) {
                    if ($request->filled('branches')) {
                        $q->whereIn('branch_id', $request->input('branches'));
                    }
                    if ($request->filled('departments')) {
                        $q->whereIn('department_id', $request->input('departments'));
                    }
                    if ($request->filled('designations')) {
                        $q->whereIn('designation_id', $request->input('designations'));
                    }
                });

                $userIds = $query->pluck('id')->toArray();
            }

            if ($request->has('skip_conflicts')) {
                $userIds = array_filter($userIds, function ($userId) {
                    return !PayrollBatchUsers::where('user_id', $userId)->exists();
                });
            }

            if ($request->has('force_include_users')) {
                $include = explode(',', $request->input('force_include_users'));
                $userIds = array_merge($userIds, $include);
                $userIds = array_unique($userIds);  
            }

            $batchIds = $request->input('payroll_batch_id', []);

            foreach ($userIds as $userId) {
                foreach ($batchIds as $batchId) {
                    PayrollBatchUsers::updateOrCreate([
                        'user_id' => $userId,
                        'pbsettings_id' => $batchId,
                    ]);
                }
            }

            DB::commit(); 
            
            return response()->json([
                'status' => 'success',
                'message' => 'Employees successfully assigned to selected payroll batches.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Batch Bulk Assign Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while assigning employees. Please try again.'
            ], 500);
        }
    }

    protected function resolveUserIdsFromFilters(Request $request)
    {
        $query = User::query();

        $query->whereHas('employmentDetail', function ($q) use ($request) {
            if ($request->filled('branches')) {
                $q->whereIn('branch_id', $request->input('branches'));
            }

            if ($request->filled('departments')) {
                $q->whereIn('department_id', $request->input('departments'));
            }

            if ($request->filled('designations')) {
                $q->whereIn('designation_id', $request->input('designations'));
            }
        });

        return $query->pluck('id')->toArray();
    }

    public function checkDuplicatePayroll(Request $request)
    {
        $userIds = $this->resolveUserIdsFromFilters($request);

        $conflictedUsers = User::whereIn('id', $userIds)
            ->whereHas('payrollBatchUsers')
            ->with(['personalInformation', 'payrollBatchUsers.batchSetting']) // include batch info
            ->get()
            ->map(function ($user) {
                $info = $user->personalInformation;
                $batch = $user->payrollBatchUsers->first()?->batchSetting?->name ?? 'Unknown Batch';

                return [
                    'user_id' => $user->id,
                    'name' => $info
                        ? $info->first_name . ' ' . ($info->middle_name ? $info->middle_name . ' ' : '') . $info->last_name
                        : 'User #' . $user->id,
                    'batch' => $batch
                ];
            })
            ->values();

        return response()->json([
            'conflicts' => $conflictedUsers
        ]);
    } 
    public function payrollBatchSettingsIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(51);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $payrollBatchSettings = $accessData['payrollBatchSettings']->withCount('batchUsers')->get();
        return view('tenant.payroll.payrollbatch.payrollbatchsettings', [ 
            'permission'=> $permission, 
            'payrollBatchSettings' => $payrollBatchSettings
        ]);
    }
    public function  payrollBatchSettingsStore(Request $request)
    {  
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        
        $permission = PermissionHelper::get(51);
        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }
        
        $request->validate([
            'batch_name' => 'required|string|max:255'
        ]);

        PayrollBatchSettings::create([
            'name' => $request->input('batch_name'),
            'tenant_id' => $tenantId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch created successfully.'
        ]);
    }
    public function payrollBatchSettingsUpdate(Request $request)
    {   
        $permission = PermissionHelper::get(51);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }
        

        $request->validate([
            'id' => 'required|exists:payroll_batch_settings,id',
            'batch_name' => 'required|string|max:255'
        ]);

        $batch = PayrollBatchSettings::findOrFail($request->input('id'));
        $batch->name = $request->input('batch_name');
        $batch->save();

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch updated successfully.', 
        ]);
    } 
    public function payrollBatchSettingsDelete(Request $request)
    {   
        $permission = PermissionHelper::get(51);
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }
        $request->validate([
            'id' => 'required|exists:payroll_batch_settings,id'
        ]);

        PayrollBatchSettings::findOrFail($request->input('id'))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch deleted successfully.'
        ]);
    }

}
