<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use App\Models\Role;
use App\Models\UserLog;
use App\Models\UserPermission;
use App\Http\Controllers\DataAccessController;
use App\Models\EmploymentPersonalInformation;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Validator;

class AffiliateBranchController extends Controller
{
    public function createAffiliateIndex()
    {
        return view('affiliate.branch.register');
    }


public function registerBranch(Request $request)
{
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name'  => 'required|string|max:255',
        'middle_name'=> 'nullable|string|max:255',
        'suffix'     => 'nullable|string|max:255',
        'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

        'username' => 'required|string|max:255|unique:users,username',
        'email'    => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:6|same:confirm_password',
        'confirm_password' => 'required|string|min:6',

        'role_id'  => 'required|integer|exists:role,id',
        'phone_number' => 'nullable|string|max:255',

        // Branch fields
        'branch_name'     => 'required|string|max:255',
        'branch_location' => 'required|string|max:500',
    ], [
        'branch_location.required' => 'The address field is required.',
    ]);

    if ($validator->fails()) {
        $firstError = $validator->errors()->first();

        \Log::error('Branch User validation failed', [
            'errors' => $validator->errors()->toArray(),
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        return response()->json([
            'message' => $firstError,
            'errors'  => $validator->errors(),
        ], 422);
    }

    \DB::beginTransaction();
    try {
        // 1. Create Branch
        $branch = Branch::create([
            'tenant_id' => 1,
            'name'      => $request->branch_name,
            'location'  => $request->branch_location,
        ]);

        // 2. Create User
        $user = new User();
        $user->username  = $request->username;  
        $user->tenant_id = 1; 
        $user->email     = $request->email;
        $user->password  = bcrypt($request->password);
        $user->save();

        // 3. Assign Role -> UserPermission
        $role = Role::find($request->role_id);

        $userPermission = new UserPermission();
        $userPermission->user_id = $user->id;
        $userPermission->role_id = $role->id;
        $userPermission->data_access_id      = $role->data_access_id ?? 2;
        $userPermission->menu_ids            = $role->menu_ids ?? null;
        $userPermission->module_ids          = $role->module_ids ?? null;
        $userPermission->user_permission_ids = $role->role_permission_ids ?? null;
        $userPermission->status = 1;
        $userPermission->save();

        // 4. Handle profile picture upload
        $profileImagePath = null;
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $filename = time() . '_' . $image->getClientOriginalName();

            $path = storage_path('app/public/profile_images');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $savePath = $path . '/' . $filename;
            $manager = new ImageManager(new Driver());
            $manager->read($image->getRealPath())
                    ->resize(300, 300)
                    ->save($savePath);

            $profileImagePath = 'profile_images/' . $filename;
        }

        // 5. Save Employment Personal Info
        EmploymentPersonalInformation::create([
            'user_id'   => $user->id,
            'first_name'=> $request->first_name,
            'last_name' => $request->last_name,
            'middle_name'=> $request->middle_name,
            'suffix'    => $request->suffix,
            'profile_picture' => $profileImagePath,
            'phone_number'    => $request->phone_number,
            // Optionally link to branch
            'branch_id'       => $branch->id,
        ]);

        \DB::commit();

        // 6. Log Action (no auth user)
        UserLog::create([
            'user_id' => null,
            'global_user_id' => null,
            'module' => 'Branch User',
            'action' => 'Create',
            'description' => 'Created new branch user and branch',
            'affected_id' => $user->id,
            'old_data' => null,
            'new_data' => json_encode([
                'username' => $user->username,
                'email'    => $user->email,
                'name'     => $request->first_name . ' ' . $request->last_name,
                'branch'   => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'location' => $branch->location,
                ],
            ]),
        ]);

        return response()->json([
            'status' => 'success',
            'message'=> 'Branch and user created successfully.',
            'branch' => $branch,
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Error creating branch and user', [
            'exception' => $e,
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        return response()->json([
            'message' => 'Error creating branch and user.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}