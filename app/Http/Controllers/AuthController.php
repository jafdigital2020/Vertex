<?php

namespace App\Http\Controllers;

use App\Models\CRUD;
use App\Models\User;
use App\Models\Tenant;
use App\Models\GlobalUser;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    // API Login
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

                return response()->json([
                    'message' => 'Super Admin login successful',
                    'token' => $token,
                    'user' => $globalUser,
                    'role' => $globalUser->global_role->global_role_name
                ]);
            }

            if (!$request->companyCode) {
                Log::warning('Login failed: Company code is required for Tenant Admins', [
                    'login' => $request->login
                ]);
                return response()->json(['message' => 'Company code is required for Tenant Admins'], 400);
            }

            $tenant = Tenant::where('tenant_code', $request->companyCode)->first();

            if (!$tenant) {
                Log::warning('Login failed: Invalid company code', [
                    'login' => $request->login,
                    'companyCode' => $request->companyCode
                ]);
                return response()->json(['message' => 'Invalid company code'], 404);
            }

            if ($globalUser->tenant->tenant_code !== $request->companyCode) {
                Log::warning('Login failed: Tenant Admin does not belong to this organization', [
                    'login' => $request->login,
                    'companyCode' => $request->companyCode
                ]);
                return response()->json(['message' => 'Unauthorized: Tenant Admin does not belong to this organization'], 403);
            }

            Auth::guard('global')->login($globalUser);

            $token = $globalUser->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Tenant Admin login successful',
                'token' => $token,
                'user' => $globalUser,
                'tenant' => $tenant,
                'role' => $globalUser->global_role->global_role_name
            ]);
        } else {
            if (!$request->companyCode) {
                Log::warning('Login failed: Company code is required', [
                    'login' => $request->login
                ]);
                return response()->json(['message' => 'Company code is required'], 400);
            }
            $tenant = Tenant::where('tenant_code', $request->companyCode)->first();
            if (!$tenant) {
                Log::warning('Login failed: Invalid company code', [
                    'login' => $request->login,
                    'companyCode' => $request->companyCode
                ]);
                return response()->json(['message' => 'Invalid company code'], 404);
            }

            $tenantUser = User::where($fieldType, $request->login)
                ->where('tenant_id',  $tenant->id)
                ->first();

            if (!$tenantUser) {
                Log::warning('Login failed: Invalid username or email.', [
                    'login' => $request->login,
                    'companyCode' => $request->companyCode
                ]);
                return response()->json([
                    'message' => 'Invalid username or email.',
                    'type' => 'login'
                ], 401);
            }

            // Move password check outside of tenantUser existence check
            if (!Hash::check($request->password, $tenantUser->password)) {
                Log::warning('Login failed: Invalid password.', [
                    'login' => $request->login,
                    'companyCode' => $request->companyCode
                ]);
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
                'tenant' => $tenant,
                'role' => 'tenant_user'
            ]);
        }
    }

    // logout
    public function logout(Request $request)
    {
        // For API logout: revoke token and clear session
        if ($request->expectsJson() || $request->wantsJson()) {
            $user = $request->user();

            // Revoke ALL tokens for this user
            if ($user) {
                $user->tokens()->delete();
                $user->remember_token = null;
                $user->save();
            }

            // Clear session data
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Clear all guards
            Auth::guard('web')->logout();
            Auth::guard('global')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }

        // For web logout
        $user = Auth::user() ?? Auth::guard('global')->user();
        if ($user) {
            $user->remember_token = null;
            $user->save();

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
        }

        Auth::guard('web')->logout();
        Auth::guard('global')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function verifyToken(Request $request)
    {
        // Sanctum automatically validates the token via middleware
        return response()->json(['valid' => true, 'user' => $request->user()]);
    }
}
