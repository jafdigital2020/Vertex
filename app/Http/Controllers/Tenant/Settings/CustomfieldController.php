<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Models\UserLog;
use App\Models\CustomField;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class CustomfieldController extends Controller
{  
    
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }  
  
    public function customfieldIndex(Request $request)
    {
       // Auth User Tenant ID
        $authUser = $this->authUser();  
        $permission = PermissionHelper::get(43);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);  
        
        $tenantId = $authUser->tenantId ?? null;
        $customFields = $accessData['customFields']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Custom fields index',
                'data' => $customFields
            ]);
        }

        return view('tenant.settings.customfields', [
            'customFields' => $customFields,
            'permission'=> $permission
        ]);
    }

    public function customfieldCreate(Request $request)
    { 
        $permission = PermissionHelper::get(43);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        $validated = $request->validate([
            'prefix_name' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $prefix = CustomField::create([
            'prefix_name' => $validated['prefix_name'],
            'remarks' => $validated['remarks'] ?? null,
            'tenant_id' => Auth::user()->tenant_id ?? null,
        ]);

        // Save user log
        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Prefix',
            'action'      => 'create',
            'description' => 'Created new prefix: ' . $prefix->prefix_name,
            'affected_id' => $prefix->id,
            'old_data'    => null,
            'new_data'    => json_encode($prefix),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Prefix added successfully.',
            'data' => $prefix
        ]);
    }

    // Update custom field
    public function customfieldUpdate(Request $request, $id)
    {   
        $permission = PermissionHelper::get(43);

        if (!in_array('Update', $permission) ) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }
        $validated = $request->validate([
            'prefix_name' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $prefix = CustomField::findOrFail($id);
        $oldData = $prefix->toArray();

        $prefix->update([
            'prefix_name' => $validated['prefix_name'],
            'remarks' => $validated['remarks'],
        ]);

        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Prefix',
            'action'      => 'update',
            'description' => 'Updated prefix: ' . $prefix->prefix_name,
            'affected_id' => $prefix->id,
            'old_data'    => json_encode($oldData),
            'new_data'    => json_encode($prefix),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Prefix updated successfully.',
            'data' => $prefix
        ]);
    }

    // Delete custom field
    public function customfieldDelete($id)
    {   
        $permission = PermissionHelper::get(43);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete.'
            ], 403);
        }

        $prefix = CustomField::findOrFail($id);
        $prefix->delete();

        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Prefix',
            'action'      => 'delete',
            'description' => 'Deleted prefix: ' . $prefix->prefix_name,
            'affected_id' => $prefix->id,
            'old_data'    => json_encode($prefix),
            'new_data'    => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Prefix deleted successfully.',
        ]);
    }
}
