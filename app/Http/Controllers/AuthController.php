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

    // API Login
    public function apiLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Username or email
            'password' => 'required',
            'companyCode' => 'nullable' // Required for Tenant Admin & Users
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // ✅ Step 1: Check if User Exists in Global Users (Super Admin or Tenant Admin)
        $globalUser = GlobalUser::where($fieldType, $request->login)->first();

        if ($globalUser && Hash::check($request->password, $globalUser->password)) {
            if ($globalUser->role === 'super_admin') {
                // ✅ Super Admin does NOT need a company code
                Auth::guard('global')->login($globalUser);
                $token = $globalUser->createToken('authToken')->plainTextToken;

                return response()->json([
                    'message' => 'Super Admin login successful',
                    'token' => $token,
                    'user' => $globalUser,
                    'role' => 'super_admin'
                ]);
            }

            // ✅ Step 2: Tenant Admins MUST provide company code
            if (!$request->companyCode) {
                return response()->json(['message' => 'Company code is required for Tenant Admins'], 400);
            }

            // ✅ Step 3: Validate Organization Code
            $organization = Organization::where('code', $request->companyCode)->first();

            if (!$organization) {
                return response()->json(['message' => 'Invalid company code'], 404);
            }

            // ✅ Step 4: Validate Tenant Admin belongs to this Organization
            if ($globalUser->organization_code !== $request->companyCode) {
                return response()->json(['message' => 'Unauthorized: Tenant Admin does not belong to this organization'], 403);
            }

            // ✅ Authenticate Tenant Admin
            Auth::guard('global')->login($globalUser);
            $token = $globalUser->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Tenant Admin login successful',
                'token' => $token,
                'user' => $globalUser,
                'organization' => $organization,
                'role' => 'tenant_admin'
            ]);
        }

        // ✅ Step 5: If Not Found in Global Users, Check Tenant Users (Company Code Required)
        if (!$request->companyCode) {
            return response()->json(['message' => 'Company code is required'], 400);
        }

        // ✅ Find Organization
        $organization = Organization::where('code', $request->companyCode)->first();
        if (!$organization) {
            return response()->json(['message' => 'Invalid company code'], 404);
        }

        // ✅ Check if Tenant User Exists in the Main `users` Table (No DB Switching)
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

        // ✅ Authenticate Tenant User
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

    // public function apiLogin(Request $request)
    // {
    //     $request->validate([
    //         'login' => 'required|string', // Username or email
    //         'password' => 'required',
    //         'companyCode' => 'nullable' // Optional for Super Admin
    //     ]);

    //     $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    //     // ✅ Step 1: Check if User Exists in Global Users (Super Admin)
    //     $globalUser = GlobalUser::where($fieldType, $request->login)->first();

    //     if ($globalUser && Hash::check($request->password, $globalUser->password)) {
    //         // ✅ Authenticate Super Admin using 'global' guard
    //         Auth::guard('global')->login($globalUser);

    //         // ✅ Generate API Token
    //         $token = $globalUser->createToken('authToken')->plainTextToken;

    //         return response()->json([
    //             'message' => 'Super Admin login successful',
    //             'token' => $token,
    //             'user' => $globalUser,
    //             'role' => 'super_admin'
    //         ]);
    //     }

    //     // ✅ Step 2: If not Super Admin, check for companyCode
    //     if (!$request->companyCode) {
    //         return response()->json(['message' => 'Company code is required'], 400);
    //     }

    //     // ✅ Step 3: Find the Organization and Switch to Tenant Database
    //     $organization = Organization::where('code', $request->companyCode)->first();

    //     if (!$organization) {
    //         return response()->json(['message' => 'Organization not found'], 404);
    //     }

    //     // ✅ Step 3.1: Check if Organization Subscription is Expired
    //     $latestSubscription = $organization->subscriptions()->latest()->first();

    //     if (!$latestSubscription || now()->greaterThan($latestSubscription->subscription_end)) {
    //         return response()->json([
    //             'message' => 'Your subscription has expired. Please renew to continue.',
    //             'status' => 'expired'
    //         ], 403);
    //     }

    //     // 🔄 Switch Database Connection to Tenant
    //     config(['database.connections.tenant.database' => $organization->database_name]);
    //     DB::purge('tenant'); // Clears old connection
    //     DB::reconnect('tenant'); // Reconnects with new database

    //     // ✅ Step 4: Authenticate User from Tenant Database
    //     $tenantUser = DB::connection('tenant')->table('users')->where($fieldType, $request->login)->first();

    //     if (!$tenantUser || !Hash::check($request->password, $tenantUser->password)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     // ✅ Generate Token for Tenant User
    //     $userModel = new User();
    //     $userModel->forceFill((array) $tenantUser); // Convert DB object to model
    //     Auth::guard('tenant')->login($userModel); // Authenticate tenant user

    //     $token = $userModel->createToken('authToken')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login successful',
    //         'token' => $token,
    //         'user' => $tenantUser,
    //         'organization' => $organization,
    //         'role' => 'tenant_admin'
    //     ], 200);
    // }
}
