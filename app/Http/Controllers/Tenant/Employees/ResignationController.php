<?php

namespace App\Http\Controllers\Tenant\Employees;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Resignation;
use Illuminate\Http\Request;
use App\Models\ResignationHR;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DataAccessController;

class ResignationController extends Controller
{ 
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        return Auth::user();
    }
    public function resignationAdminIndex(Request $request)
    {   
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(22); 
        $resignations = Resignation::with([
            'personalInformation',
            'employmentDetail.branch',
            'employmentDetail.department.designations',
        ])
        ->whereHas('employmentDetail.department', function ($q) use ($authUser) {
            $q->where('head_of_department', $authUser->id);
        })
        ->orWhereHas('employmentDetail', function ($q) use ($authUser) {
            $q->where('reporting_to', $authUser->id);
        })
        ->get();
        return view('tenant.resignation.resignation-admin',['permission' => $permission , 'resignations'=> $resignations]);
    }
    public function resignationEmployeeIndex(Request $request)
    {  
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(58);
        $resignations  = Resignation::where('user_id',$authUser->id)->with('personalInformation')->get();
        
        return view('tenant.resignation.resignation-employee',['permission' => $permission, 'resignations'=> $resignations]);
    }

public function submitResignation(Request $request)
{
    $authUser = $this->authUser();

    try { 
        DB::beginTransaction();

        $request->validate([
            'resignation_letter' => 'required|mimes:pdf,doc,docx|max:2048',
            'resignation_reason' => 'nullable|string|max:500',
        ]);

        $fileName = time() . '_' . $request->file('resignation_letter')->getClientOriginalName();
 
        $request->file('resignation_letter')->move(public_path('storage/resignation_letters'), $fileName);
 
        Resignation::create([
            'date_filed'       => Carbon::now(),
            'user_id'          => $authUser->id,
            'resignation_file' => 'resignation_letters/' . $fileName,
            'reason'           => $request->resignation_reason, 
            'status'           => 0, 
        ]);

        DB::commit();

        return redirect()->back()->with('success', 'Resignation submitted successfully.');

    } catch (\Exception $e) {
        DB::rollBack();

        // Log the error for debugging
        Log::error('Resignation submission failed: ' . $e->getMessage(), [
            'user_id' => $authUser->id ?? null,
            'trace'   => $e->getTraceAsString(),
        ]);

        return redirect()->back()->with('error', 'Something went wrong while submitting your resignation. Please try again.');
    }
}

 
public function update(Request $request, $id)
{
    $resignation = Resignation::findOrFail($id);

    if ($request->hasFile('resignation_letter')) {
        $file = $request->file('resignation_letter');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('resignations', $fileName, 'public');
        $resignation->resignation_file = 'resignations/' . $fileName;
    }

    $resignation->reason = $request->input('resignation_reason');
    $resignation->save();

    return response()->json(['message' => 'Resignation updated successfully!']);
}


public function destroy($id)
{   
    Log::info('sdasdasd');
    try {
        $resignation = Resignation::findOrFail($id);

        $resignation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resignation deleted successfully.'
        ]);

    } catch (\Exception $e) {
      
        Log::error('Resignation deletion failed', [
            'resignation_id' => $id,
            'error_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString() 
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete resignation. Please contact support.'
        ], 500);
    }
}

 
public function approve(Request $request, $id)
{
    $request->validate([
        'status_remarks' => 'nullable|string|max:500',
    ]);
    Log::info('yesssss');
    try {
        DB::beginTransaction();

        $resignation = Resignation::findOrFail($id);
        $resignation->status = 1;  
        $resignation->status_remarks = $request->status_remarks;
        $resignation->status_date = Carbon::now();
        $resignation->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Resignation has been approved successfully.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
 
        Log::error('Error approving resignation', [
            'resignation_id' => $id,
            'user_id' => auth()->id(),
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred while approving resignation.'
        ], 500);
    }
}

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $resignation = Resignation::findOrFail($id);
            $resignation->status = 2; // rejected
            $resignation->status_remarks = $request->remarks; 
            $resignation->status_date = Carbon::now();
            $resignation->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resignation has been rejected successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        } 
}

    public function acceptByHR(Request $request, $id)
    {   

        $authUser = $this->authUser();

        $request->validate([
            'accepted_remarks' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $resignation = Resignation::findOrFail($id);
    
            if ($resignation->status !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resignation must be approved by the department head/ reporting to first.'
                ], 400);
            } 
            $resignation->resignation_date = $request->resignation_date; 
            $resignation->accepted_date = now();
            $resignation->accepted_by = $authUser->id; 
            $resignation->accepted_remarks = $request->accepted_remarks;
            $resignation->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resignation has been accepted by HR successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting resignation by HR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRemarks($id)
    {
        $resignation = Resignation::find($id);

        if (!$resignation) {
            return response()->json(['success' => false, 'message' => 'Resignation not found.']);
        }

        return response()->json([
            'success' => true,
            'status_remarks' => $resignation->status_remarks,
            'accepted_remarks' => $resignation->accepted_remarks,
        ]);
    }
    
    
    public function resignationSettingsIndex(Request $request)
    {  
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(59);
        $resignationHR = ResignationHR::all();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->where('name','Theos Helios Security Agency Corp')->get(); 
        $branchIds = $branches->pluck('id')->toArray(); 
        $departments = $accessData['departments']->whereIn('branch_id',$branchIds)->get(); 
        $departmentIds = $departments->pluck('id')->toArray(); 
        $designations = $accessData['designations']->whereIn('department_id', $departmentIds)->get();
        
        return view('tenant.resignation.resignation-settings',['resignationHR' => $resignationHR,'permission' => $permission,'branches' =>$branches, 'departments' => $departments, 'designations'=> $designations]);
    }
    public function assignMultiple(Request $request)
    {
        $request->validate([
            'hr_ids' => 'required|array|min:1',
        ]);

        foreach ($request->hr_ids as $hrId) {
            ResignationHR::updateOrCreate(
                ['hr_id' => $hrId],
                [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => 'active',
                ]
            );
        }

        return response()->json(['message' => 'Selected HRs successfully assigned!']);
    }
    public function getDepartmentsByBranch($branchId)
        {
            $departments = Department::where('branch_id', $branchId)->get(['id', 'department_name']);
            Log::info('etoo:' . $departments);
            return response()->json($departments);
        }

        public function getDesignationsByDepartment($departmentId)
        {
            $designations = Designation::where('department_id', $departmentId)->get(['id', 'designation_name']);
            return response()->json($designations);
        }

        public function getEmployeesByDesignation($designationId)
        {
            $employees = User::where('designation_id', $designationId)->get(['id', 'name']);
            return response()->json($employees);
        }
    }
