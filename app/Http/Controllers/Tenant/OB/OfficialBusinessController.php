<?php

namespace App\Http\Controllers\Tenant\OB;

use Carbon\Carbon;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\OfficialBusiness;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OfficialBusinessController extends Controller
{
    public function employeeOBIndex(Request $request)
    {
        $authUserId = Auth::user()->id ?? null;

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
        ]);
    }

    // Request OB (Employee)
    public function employeeRequestOB(Request $request)
    {
        // Validation
        $request->validate([
            'ob_date'      => 'required|date',
            'date_ob_in'         => 'required|date',
            'date_ob_out'        => 'required|date|after_or_equal:date_ob_in',
            'total_ob_minutes'   => 'nullable|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'purpose'            => 'required|string|max:255',
        ]);

        $authUserId = Auth::user()->id;

        // Check if an official business entry exists for this user & date
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
