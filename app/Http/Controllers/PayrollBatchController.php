<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\PayrollBatchUsers;
use Illuminate\Support\Facades\DB;
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
 
        $query  = $accessData['employees'];

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
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $users = $accessData['employees']->get();
        $payrollBatchSettings = $accessData['payrollBatchSettings']->get();
   
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

        PayrollBatchSettings::findOrFail($request->nput('id'))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch deleted successfully.'
        ]);
    }

}
