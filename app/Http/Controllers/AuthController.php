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
use App\Traits\ResponseTimingTrait;

class AuthController extends Controller
{
     use ResponseTimingTrait;
    public function loginIndex()
    {
        return view('auth.login');
    }


    // API Login
    public function apiLogin(Request $request)
    {
        $startTime = microtime(true);
        try {
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

                     return $this->timedResponse([
                    'message' => 'Super Admin login successful',
                    'token' => $token,
                    'user' => $globalUser,
                    'role' => $globalUser->global_role->global_role_name
                ], 200, $startTime);
                }

                if (!$request->companyCode) {
                    // Log this error to your Node.js system
                    $errorMessage = 'Login failed: Company code is required for Tenant Admins. Login attempt: ' . $request->login;
                    \App\Helpers\ErrorLogger::logToRemoteSystem(
                        $errorMessage, 
                        null, // client_name
                        null  // client_id
                    );
                    
                    Log::warning('Login failed: Company code is required for Tenant Admins', [
                        'login' => $request->login
                    ]);
                    
                     return $this->timedResponse([
                    'status' => false,
                    'message' => 'Company code is required for Tenant Admins',
                    'error_logged' => true
                ], 400, $startTime);
                }

                $tenant = Tenant::where('tenant_code', $request->companyCode)->first();

                if (!$tenant) {
                    // Log this error
                    $errorMessage = 'Login failed: Invalid company code. Code: ' . $request->companyCode . ' | Login: ' . $request->login;
                    \App\Helpers\ErrorLogger::logToRemoteSystem(
                        $errorMessage,
                        null, // We don't know the client name yet
                        null  // We don't know the client ID yet
                    );
                    
                    Log::warning('Login failed: Invalid company code', [
                        'login' => $request->login,
                        'companyCode' => $request->companyCode
                    ]);
                    
                    return $this->timedResponse([
                    'status' => false,
                    'message' => 'Invalid company code',
                    'error_logged' => true
                ], 404, $startTime);
                }

                if ($globalUser->tenant->tenant_code !== $request->companyCode) {
                    // Log this error with client info
                    $errorMessage = 'Login failed: Tenant Admin does not belong to this organization. ' .
                                'Admin: ' . $globalUser->email . ' | Attempted Company: ' . $request->companyCode .
                                ' | Actual Company: ' . $globalUser->tenant->tenant_code;
                    \App\Helpers\ErrorLogger::logToRemoteSystem(
                        $errorMessage,
                        $tenant->tenant_name,
                        $tenant->id
                    );
                    
                    Log::warning('Login failed: Tenant Admin does not belong to this organization', [
                        'login' => $request->login,
                        'companyCode' => $request->companyCode
                    ]);
                    
                   return $this->timedResponse([
                    'status' => false,
                    'message' => 'Unauthorized: Tenant Admin does not belong to this organization',
                    'error_logged' => true,
                    'client_name' => $tenant->tenant_name
                ], 403, $startTime);
                }

                Auth::guard('global')->login($globalUser);
                $token = $globalUser->createToken('authToken')->plainTextToken;

                return $this->timedResponse([
                'message' => 'Tenant Admin login successful',
                'token' => $token,
                'user' => $globalUser,
                'tenant' => $tenant,
                'role' => $globalUser->global_role->global_role_name
            ], 200, $startTime);
            } else {
                if (!$request->companyCode) {
                    // Log this error
                    $errorMessage = 'Login failed: Company code is required. Login: ' . $request->login;
                    \App\Helpers\ErrorLogger::logToRemoteSystem($errorMessage);
                    
                    Log::warning('Login failed: Company code is required', [
                        'login' => $request->login
                    ]);
                    
                    return $this->timedResponse([
                    'status' => false,
                    'message' => 'Company code is required',
                    'error_logged' => true
                ], 400, $startTime);
                }
                
                $tenant = Tenant::where('tenant_code', $request->companyCode)->first();
                
                if (!$tenant) {
                    // Log this error
                    $errorMessage = 'Login failed: Invalid company code. Code: ' . $request->companyCode . ' | Login: ' . $request->login;
                    \App\Helpers\ErrorLogger::logToRemoteSystem($errorMessage);
                    
                    Log::warning('Login failed: Invalid company code', [
                        'login' => $request->login,
                        'companyCode' => $request->companyCode
                    ]);
                    
                    return $this->timedResponse([
                    'status' => false,
                    'message' => 'Invalid company code',
                    'error_logged' => true
                ], 404, $startTime);
                }

                $tenantUser = User::where($fieldType, $request->login)
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if (!$tenantUser) {
                    // Log this error with client info
                    $errorMessage = 'Login failed: Invalid username or email. ' .
                                'Login: ' . $request->login . ' | Company Code: ' . $request->companyCode;
                    \App\Helpers\ErrorLogger::logToRemoteSystem(
                        $errorMessage,
                        $tenant->tenant_name,
                        $tenant->id
                    );
                    
                    Log::warning('Login failed: Invalid username or email.', [
                        'login' => $request->login,
                        'companyCode' => $request->companyCode
                    ]);
                    
                    return $this->timedResponse([
                    'status' => false,
                    'message' => 'Invalid username or email.',
                    'type' => 'login',
                    'error_logged' => true,
                    'client_name' => $tenant->tenant_name
                ], 401, $startTime);
                }

                // Move password check outside of tenantUser existence check
                if (!Hash::check($request->password, $tenantUser->password)) {
                    // Log this error with client info
                    $errorMessage = 'Login failed: Invalid password. ' .
                                'User: ' . $tenantUser->email . ' | Company: ' . $request->companyCode;
                    \App\Helpers\ErrorLogger::logToRemoteSystem(
                        $errorMessage,
                        $tenant->tenant_name,
                        $tenant->id
                    );
                    
                    Log::warning('Login failed: Invalid password.', [
                        'login' => $request->login,
                        'companyCode' => $request->companyCode
                    ]);
                    
                    return $this->timedResponse([
                    'status' => false,
                    'message' => 'Invalid password.',
                    'type' => 'password',
                    'error_logged' => true,
                    'client_name' => $tenant->tenant_name
                ], 401, $startTime);
                }

                Auth::guard('web')->login($tenantUser);
                $token = $tenantUser->createToken('authToken')->plainTextToken;

                return $this->timedResponse([
                'message' => 'Tenant User login successful',
                'token' => $token,
                'user' => $tenantUser,
                'tenant' => $tenant,
                'role' => 'tenant_user'
            ], 200, $startTime);
            }
            
        } catch (\Exception $e) {
            // This catches any unexpected 500 errors
            $errorMessage = 'Unexpected login error: ' . $e->getMessage() . 
                            ' | Login: ' . ($request->login ?? 'N/A') . 
                            ' | Company Code: ' . ($request->companyCode ?? 'N/A') .
                            ' | URL: ' . $request->fullUrl();
            
            // Try to get tenant info for client identification
            $clientId = null;
            $clientName = null;
            
            if ($request->companyCode) {
                $tenant = Tenant::where('tenant_code', $request->companyCode)->first();
                if ($tenant) {
                    $clientName = $tenant->tenant_name;
                }
            }
            
            // Also, check if we have a user from the request
            if (empty($clientName)) {
                // Try to get from global user if available
                $globalUser = GlobalUser::where('email', $request->login)
                            ->orWhere('username', $request->login)
                            ->first();
                
                if ($globalUser && $globalUser->tenant) {
                    $clientName = $globalUser->tenant->tenant_name;
                }
            }
            
            // Log to remote error management system
            // Only send client_name, let Node.js handle the ID
            \App\Helpers\ErrorLogger::logToRemoteSystem($errorMessage, $clientName, null);
            
            // Also log locally
            Log::error('Unexpected error in apiLogin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'login' => $request->login,
                'companyCode' => $request->companyCode,
                'client_name' => $clientName,
                'tenant_id' => $tenant->id ?? null
            ]);

            return $this->timedResponse([
                'status' => false,
                'message' => 'An unexpected error occurred during login.',
                'error_logged' => true,
                'client_name' => $clientName
            ], 500, $startTime);
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

    public function testErrorLogging(Request $request)
{
    try {
        // Simulate a 500 error
        throw new \Exception('Test 500 error from Laravel authentication system');
        
    } catch (\Exception $e) {
        // Get user info for client identification
        $clientId = null;
        $clientName = null;
        
        // Try to get from authenticated user
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
            if ($user && $user->tenant) {
                $clientId = $user->tenant->id;
                $clientName = $user->tenant->tenant_name;
            }
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user && $user->tenant) {
                $clientId = $user->tenant->id;
                $clientName = $user->tenant->tenant_name;
            }
        }
        
        // Format error message
        $errorMessage = '[' . $request->method() . ' ' . $request->fullUrl() . '] ' . 
                       $e->getMessage() . 
                       ' | User: ' . ($user->email ?? 'guest') .
                       ' | Client: ' . ($clientName ?? 'unknown');
        
        // Log to remote error management system
        \App\Helpers\ErrorLogger::logToRemoteSystem($errorMessage, $clientName, $clientId);
        
        // Also log locally
        Log::error('Test error occurred', [
            'error' => $e->getMessage(),
            'client_id' => $clientId,
            'client_name' => $clientName,
            'url' => $request->fullUrl(),
            'user' => $user->email ?? 'guest'
        ]);

        return response()->json([
            'status' => false,
            'message' => 'A test error occurred. This error has been logged to the error management system.',
            'error' => $e->getMessage(),
            'logged_to_ems' => true,
            'client_name' => $clientName,
            'client_id' => $clientId
        ], 500);
    }
}
}
