<?php

namespace App\Http\Controllers;

use App\Models\CRUD;
use App\Models\User;
use App\Models\GlobalUser;
use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Login Form
    public function loginIndex()
    {  
        return view('auth.login');
    }
    public function logout(Request $request)
    {
        Auth::logout(); 
        $request->session()->flush(); 
        $request->session()->regenerateToken(); 
        return redirect('/login'); 
    }
    public function apiLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',  
            'password' => 'required',
            'companyCode' => 'nullable'  
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
 
        $globalUser = GlobalUser::where($fieldType, $request->login)->first();

        if ($globalUser && Hash::check($request->password, $globalUser->password)) {

            if ($globalUser->global_role->global_role_name === 'super_admin') { 

                Auth::guard('global')->login($globalUser);
                $token = $globalUser->createToken('authToken')->plainTextToken; 

                Session::put('role_data', [
                    'role_id' => "global_user",
                    'menu_ids' => [],
                    'module_ids' => [],
                    'user_permission_ids' => [],
                    'status' => null
                ]);

                return response()->json([
                    'message' => 'Super Admin login successful',
                    'token' => $token,
                    'user' => $globalUser,
                    'role' => $globalUser->global_role->global_role_name
                ]);
            }
 
            if (!$request->companyCode) {
                return response()->json(['message' => 'Company code is required for Tenant Admins'], 400);
            }
 
            $tenant = Tenant::where('tenant_code', $request->companyCode)->first();

            if (!$tenant) {
                return response()->json(['message' => 'Invalid company code'], 404);
            }
 
            if ($globalUser->tenant->tenant_code !== $request->companyCode) {
                return response()->json(['message' => 'Unauthorized: Tenant Admin does not belong to this organization'], 403);
            } 

            Auth::guard('global')->login($globalUser); 

            $token = $globalUser->createToken('authToken')->plainTextToken;

            Session::put('role_data', [
                'role_id' => "global_user",
                'menu_ids' => [],
                'module_ids' => [],
                'user_permission_ids' => [],
                'status' => null
            ]);

            return response()->json([
                'message' => 'Tenant Admin login successful',
                'token' => $token,
                'user' => $globalUser,
                'tenant' => $tenant,
                'role' => $globalUser->global_role->global_role_name
            ]);

        } else {
            if (!$request->companyCode) {
                return response()->json(['message' => 'Company code is required'], 400);
            }
            $tenant = Tenant::where('tenant_code', $request->companyCode)->first();
            if (!$tenant) {
                return response()->json(['message' => 'Invalid company code'], 404);
            }
    
            $tenantUser = User::where($fieldType, $request->login)
                ->where('tenant_id',  $tenant->id)
                ->first();

            if (!$tenantUser) {
                return response()->json([
                    'message' => 'Invalid username or email.',
                    'type' => 'login'
                ], 401);
            }

            if (!Hash::check($request->password, $tenantUser->password)) {
                return response()->json([
                    'message' => 'Invalid password.',
                    'type' => 'password'
                ], 401);
            }
    
            Auth::guard('web')->login($tenantUser);
            $token = $tenantUser->createToken('authToken')->plainTextToken;
            $user =  Auth::user()->userPermission; 
 
            $crudMap = CRUD::pluck('control_name', 'id')->toArray();  

            $rawPermissions = explode(',', $user->user_permission_ids);
            $permissions = [];

            foreach ($rawPermissions as $entry) {
                [$moduleId, $crudId] = explode('-', $entry);

                if (!isset($permissions[$moduleId])) {
                    $permissions[$moduleId] = [];
                }

                $permissions[$moduleId][] = $crudMap[(int) $crudId] ?? "Unknown";
            }
 
            foreach ($permissions as $moduleId => $actions) {
                $permissions[$moduleId] = array_unique($actions);
            }
 
            Session::put('role_data', [
                'role_id' => $user->userPermission->role_id,
                'menu_ids' => explode(',', $user->menu_ids),
                'module_ids' => explode(',', $user->module_ids),
                'user_permission_ids' => $permissions,
                'status' => $user->status
            ]);

            return response()->json([
                'message' => 'Tenant User login successful',
                'token' => $token,
                'user' => $tenantUser,
                'tenant' => $tenant,
                'role' => 'tenant_user'
            ]);
        } 
    }
 
        
}
