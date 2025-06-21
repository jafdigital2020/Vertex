<?php

namespace App\Http\Controllers\Tenant\Bank;

use App\Models\Bank;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function bankIndex(Request $request)
    {
        // Get the tenant ID for Filtering of tenant
        $tenantId = Auth::user()->tenant_id ?? null;

        $banks = Bank::where('tenant_id', $tenantId)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the bank index page.',
                'data' => $banks,
            ]);
        }

        return view('tenant.bank.bank', compact('banks'));
    }

    // Bank Create
    public function bankCreate(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'required|string|max:10|unique:banks,bank_code',
            'bank_account_number' => 'required|string|max:20|unique:banks,bank_account_number',
            'bank_remarks' => 'nullable|string|max:500',
        ]);

        // Get the tenant ID from the authenticated user
        $tenantId = Auth::user()->tenant_id ?? null;

        // Create the bank record
        $bank = Bank::create([
            'tenant_id' => $tenantId,
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'bank_account_number' => $request->bank_account_number,
            'bank_remarks' => $request->bank_remarks,
        ]);

        // Logging Start
        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Bank',
            'action'         => 'Create',
            'description'    => 'Created Bank "'
                . $bank->bank_name
                . '" with code "'
                . $bank->bank_code
                . '" on '
                . $bank->created_at,
            'affected_id'    => $bank->id,
            'old_data'       => null,
            'new_data'       => json_encode($bank->toArray()),
        ]);

        return response()->json([
            'message' => 'Bank created successfully.',
            'data'    => $bank,
        ], 201);
    }

    // Bank Update
    public function bankUpdate(Request $request, $id)
    {
        $bank = Bank::findOrFail($id);

        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'required|string|max:10|unique:banks,bank_code,' . $bank->id,
            'bank_account_number' => 'required|string|max:20|unique:banks,bank_account_number,' . $bank->id,
            'bank_remarks' => 'nullable|string|max:500',
        ]);

        // Update the bank record
        $bank->update([
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'bank_account_number' => $request->bank_account_number,
            'bank_remarks' => $request->bank_remarks,
        ]);

        // Logging Start
        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Bank',
            'action'         => 'Update',
            'description'    => 'Updated Bank "'
                . $bank->bank_name
                . '" with code "'
                . $bank->bank_code
                . '" on '
                . now(),
            'affected_id'    => $bank->id,
            'old_data'       => json_encode($bank->getOriginal()),
            'new_data'       => json_encode($bank->toArray()),
        ]);

        return response()->json([
            'message' => 'Bank updated successfully.',
            'data'    => $bank,
        ]);
    }

    // Bank Delete
    public function bankDelete($id)
    {
        $bank = Bank::findOrFail($id);

        // Logging Start
        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Bank',
            'action'         => 'Delete',
            'description'    => 'Deleted Bank "'
                . $bank->bank_name
                . '" with code "'
                . $bank->bank_code
                . '" on '
                . now(),
            'affected_id'    => $bank->id,
            'old_data'       => json_encode($bank->toArray()),
            'new_data'       => null,
        ]);

        // Delete the bank record
        $bank->delete();

        return response()->json([
            'message' => 'Bank deleted successfully.',
        ]);
    }
}
