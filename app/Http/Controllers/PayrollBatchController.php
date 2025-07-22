<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
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

    public function payrollBatchUsersIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(50);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
   
        return view('tenant.payroll.payrollbatch.payrollbatchusers', [ 
            'permission'=> $permission, 
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
        $request->validate([
            'batch_name' => 'required|string|max:255'
        ]);

        PayrollBatchSettings::create([
            'name' => $request->batch_name,
            'tenant_id' => $authUser->tenant_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch created successfully.'
        ]);
    }
    public function payrollBatchSettingsUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:payroll_batch_settings,id',
            'batch_name' => 'required|string|max:255'
        ]);

        $batch = PayrollBatchSettings::findOrFail($request->id);
        $batch->name = $request->batch_name;
        $batch->save();

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch updated successfully.',
            'id' => $batch->id,
            'new_name' => $batch->name
        ]);
    } 
    public function payrollBatchSettingsDelete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:payroll_batch_settings,id'
        ]);

        PayrollBatchSettings::findOrFail($request->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll Batch deleted successfully.',
            'id' => $request->id
        ]);
    }

}
