<?php

namespace App\Http\Controllers\Tenant\OB;

use Carbon\Carbon;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\OfficialBusiness;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class OfficialBusinessController extends Controller
{   
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }
       public function filter(Request $request){

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null; 
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(49);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange'); 
        $status = $request->input('status');
 
        $query  =  OfficialBusiness::where('user_id', $authUserId)
            ->orderBy('ob_date', 'desc');

         if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('ob_date', [$start, $end]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        
        if($status){
            $query->where('status', $status);
        }
        
        $obEntries = $query->get();
       
        $html = view('tenant.ob.ob-employee_filter', compact('obEntries','permission'))->render();
        return response()->json([
        'status' => 'success',
        'html' => $html
      ]);
    }


    public function employeeOBIndex(Request $request)
    {  
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(49);
        $authUserTenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $authUserId = $authUser->id ?? null;

        $obEntries = OfficialBusiness::where('user_id', $authUserId)
            ->orderBy('ob_date', 'desc')
            ->get();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total Approved OB for current month
        $totalApprovedOB = OfficialBusiness::where('user_id', $authUserId)
            ->where('status', 'approved')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->count();

        // Total Pending OB for current month
        $totalPendingOB = OfficialBusiness::where('user_id', $authUserId)
            ->where('status', 'pending')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->count();

        // Total Rejected OB for current month
        $totalRejectedOB = OfficialBusiness::where('user_id', $authUserId)
            ->where('status', 'rejected')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->count();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $obEntries,
                'totalApprovedOB' => $totalApprovedOB,
                'totalPendingOB' => $totalPendingOB,
                'totalRejectedOB' => $totalRejectedOB,
            ]);
        }

        return view('tenant.ob.ob-employee', [
            'obEntries' => $obEntries,
            'totalApprovedOB' => $totalApprovedOB,
            'totalPendingOB' => $totalPendingOB,
            'totalRejectedOB' => $totalRejectedOB,
            'permission' => $permission
        ]);
    }

    // Request OB (Employee)
    public function employeeRequestOB(Request $request)
    {
        // Validation
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(49);
        $authUserTenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
         if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }
        $request->validate([
            'ob_date'      => 'required|date',
            'date_ob_in'         => 'required|date',
            'date_ob_out'        => 'required|date|after_or_equal:date_ob_in',
            'total_ob_minutes'   => 'nullable|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'purpose'            => 'required|string|max:255',
        ]);

        $authUserId = $authUser->id;
 
        $existing = OfficialBusiness::where('user_id', $authUserId)
            ->whereDate('ob_date', $request->ob_date)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an official business entry for this date.',
            ], 422);
        }

        // File upload
        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('ob_attachments', 'public');
        }

        $ob = OfficialBusiness::create([
            'user_id'           => $authUserId,
            'ob_date'           => $request->ob_date,
            'date_ob_in'        => $request->date_ob_in,
            'date_ob_out'       => $request->date_ob_out,
            'total_ob_minutes'  => $request->total_ob_minutes,
            'file_attachment'   => $filePath,
            'purpose'           => $request->purpose,
            'status'            => 'pending',
        ]);

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Official Business',
            'action'     => 'add_official_business',
            'description' => 'Added official business, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => null,
            'new_data'   => json_encode($ob->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official business added successfully.',
            'data'    => $ob,
        ]);
    }

    // Update OB (Employee)
    public function employeeUpdateOB(Request $request, $id)
    {    
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(49);
        $authUserTenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
         if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }
        $request->validate([
            'ob_date'      => 'required|date',
            'date_ob_in'         => 'required|date',
            'date_ob_out'        => 'required|date|after:date_ob_in',
            'total_ob_minutes'   => 'required|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'purpose'            => 'required|string|max:255',
        ]);

        $ob = OfficialBusiness::findOrFail($id);

        if ($ob->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This official business entry is already approved and cannot be edited.',
            ], 403);
        }

        $authUserId = Auth::user()->id;

        // Prevent duplicate for same user & date, excluding this record
        $exists = OfficialBusiness::where('user_id', $authUserId)
            ->whereDate('ob_date', $request->ob_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an official business entry for this date.',
            ], 422);
        }

        // Save old data for logging
        $oldData = $ob->toArray();

        // Handle file upload if new file
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('ob_attachments', 'public');
            $ob->file_attachment = $filePath;
        }

        $ob->ob_date = $request->ob_date;
        $ob->date_ob_in = $request->date_ob_in;
        $ob->date_ob_out = $request->date_ob_out;
        $ob->total_ob_minutes = $request->total_ob_minutes;
        $ob->purpose = $request->purpose;

        $ob->save();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Official Business',
            'action'     => 'Edit Official Business',
            'description' => 'Edited Employee Official Busienss, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($ob->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official Business request updated successfully.',
            'data'    => $ob,
        ]);
    }

    // Delete OB (Employee)
    public function employeeDeleteOB($id)
    {   
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(49);
        $authUserTenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
         if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $ob = OfficialBusiness::findOrFail($id);

        if ($ob->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This official business entry is already approved and cannot be deleted.',
            ], 403);
        }

        $ob->delete();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Official Business',
            'action'     => 'Delete Official Business',
            'description' => 'Deleted Employee Official Busienss, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => json_encode($ob->toArray()),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official business deleted successfully.',
        ]);
    }
}
