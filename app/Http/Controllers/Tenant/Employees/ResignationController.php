<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\Resignation;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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

        // Move file
        $request->file('resignation_letter')->move(public_path('storage/resignation_letters'), $fileName);

        // Save resignation record
        Resignation::create([
            'user_id'          => $authUser->id,
            'resignation_file' => 'resignation_letters/' . $fileName,
            'reason'           => $request->resignation_reason,
            'resignation_date' => now(),
            'status'           => 0, // pending
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
 
public function approve(Request $request, $id)
{
    $request->validate([
        'status_remarks' => 'nullable|string|max:500',
    ]);
    Log::info('yesssss');
    try {
        DB::beginTransaction();

        $resignation = Resignation::findOrFail($id);
        $resignation->status = 1; // Approved
        $resignation->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Resignation has been approved successfully.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        // ✅ Log the full error details to storage/logs/laravel.log
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
            $resignation->accepted_by = auth()->id();
            $resignation->accepted_date = now();
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
    $request->validate([
        'status_remarks' => 'required|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        $resignation = Resignation::findOrFail($id);
 
        if ($resignation->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Resignation must be approved by the manager first.'
            ], 400);
        }

        $resignation->status = 3; 
        $resignation->accepted_date = now();
        $resignation->status_remarks = $request->status_remarks;
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


}
