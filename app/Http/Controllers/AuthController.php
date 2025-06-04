<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GlobalUser;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login Form
    public function loginIndex()
    {
        return view('auth.login');
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

            if ($globalUser->role === 'super_admin') { 

                Auth::guard('global')->login($globalUser);
                $token = $globalUser->createToken('authToken')->plainTextToken; 
                
                return response()->json([
                    'message' => 'Super Admin login successful',
                    'token' => $token,
                    'user' => $globalUser,
                    'role' => 'super_admin'
                ]);
            }
 
            if (!$request->companyCode) {
                return response()->json(['message' => 'Company code is required for Tenant Admins'], 400);
            }
 
            $organization = Organization::where('code', $request->companyCode)->first();

            if (!$organization) {
                return response()->json(['message' => 'Invalid company code'], 404);
            }
 
            if ($globalUser->organization_code !== $request->companyCode) {
                return response()->json(['message' => 'Unauthorized: Tenant Admin does not belong to this organization'], 403);
            } 

            Auth::guard('global')->login($globalUser); 

            $token = $globalUser->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Tenant Admin login successful',
                'token' => $token,
                'user' => $globalUser,
                'organization' => $organization,
                'role' => 'tenant_admin'
            ]);

        } else {
            if (!$request->companyCode) {
                return response()->json(['message' => 'Company code is required'], 400);
            }
    
            $organization = Organization::where('code', $request->companyCode)->first();
            if (!$organization) {
                return response()->json(['message' => 'Invalid company code'], 404);
            }
    
            $tenantUser = User::where($fieldType, $request->login)
                ->where('organization_code', $request->companyCode)
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
            
            return response()->json([
                'message' => 'Tenant User login successful',
                'token' => $token,
                'user' => $tenantUser,
                'organization' => $organization,
                'role' => 'tenant_user'
            ]);
        } 
    }
 
        
}
