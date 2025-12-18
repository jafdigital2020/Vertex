<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MobileAccessAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    /**
     * Mobile login for employees with mobile access license.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
                'tenant_id' => 'required|string',
            ]);

            // Find the user in the specified tenant
            $user = User::where('username', $request->username)
                ->where('tenant_id', $request->tenant_id)
                ->first();

            // Validate credentials
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            // Check if user is active
            if (!$user->active_license) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact your administrator.',
                    'error_code' => 'ACCOUNT_INACTIVE'
                ], 403);
            }

            // Check if user has active mobile access license
            $mobileAccess = MobileAccessAssignment::forTenant($request->tenant_id)
                ->forUser($user->id)
                ->active()
                ->first();

            if (!$mobileAccess) {
                Log::warning('Mobile access denied - no license', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'tenant_id' => $request->tenant_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'You do not have mobile access. Please contact your administrator to get a mobile access license.',
                    'error_code' => 'NO_MOBILE_ACCESS'
                ], 403);
            }

            // Create API token for mobile access
            $token = $user->createToken('mobile-access', ['mobile'])->plainTextToken;

            Log::info('Mobile login successful', [
                'user_id' => $user->id,
                'username' => $user->username,
                'tenant_id' => $request->tenant_id,
                'mobile_access_assignment_id' => $mobileAccess->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'tenant_id' => $user->tenant_id,
                        'full_name' => $user->personalInformation->full_name ?? $user->username,
                        'email' => $user->email,
                        'department' => $user->employmentDetail->department->department_name ?? null,
                        'designation' => $user->employmentDetail->designation->designation_name ?? null,
                    ],
                    'mobile_access' => [
                        'assigned_at' => $mobileAccess->assigned_at,
                        'status' => $mobileAccess->status,
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Mobile login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->only(['username', 'tenant_id'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login. Please try again.',
                'error_code' => 'LOGIN_ERROR'
            ], 500);
        }
    }

    /**
     * Logout and revoke mobile access token.
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();

            Log::info('Mobile logout successful', [
                'user_id' => $user->id,
                'username' => $user->username,
                'tenant_id' => $user->tenant_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful.',
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile logout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout.',
            ], 500);
        }
    }

    /**
     * Validate mobile access and get user info.
     */
    public function validateAccess(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Re-check mobile access (in case it was revoked after login)
            $mobileAccess = MobileAccessAssignment::forTenant($user->tenant_id)
                ->forUser($user->id)
                ->active()
                ->first();

            if (!$mobileAccess) {
                // Revoke the current token since access was removed
                $request->user()->currentAccessToken()->delete();

                Log::warning('Mobile access revoked during session', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'tenant_id' => $user->tenant_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Your mobile access has been revoked. Please contact your administrator.',
                    'error_code' => 'ACCESS_REVOKED'
                ], 403);
            }

            // Check if user account is still active
            if (!$user->active_license) {
                $request->user()->currentAccessToken()->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact your administrator.',
                    'error_code' => 'ACCOUNT_DEACTIVATED'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Access validated.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'tenant_id' => $user->tenant_id,
                        'full_name' => $user->personalInformation->full_name ?? $user->username,
                        'email' => $user->email,
                        'department' => $user->employmentDetail->department->department_name ?? null,
                        'designation' => $user->employmentDetail->designation->designation_name ?? null,
                        'active_license' => $user->active_license,
                    ],
                    'mobile_access' => [
                        'assigned_at' => $mobileAccess->assigned_at,
                        'status' => $mobileAccess->status,
                    ],
                    'token_valid_until' => $request->user()->currentAccessToken()->expires_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile access validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during access validation.',
            ], 500);
        }
    }

    /**
     * Refresh mobile access token.
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Validate current mobile access
            $mobileAccess = MobileAccessAssignment::forTenant($user->tenant_id)
                ->forUser($user->id)
                ->active()
                ->first();

            if (!$mobileAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile access has been revoked.',
                    'error_code' => 'ACCESS_REVOKED'
                ], 403);
            }

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $newToken = $user->createToken('mobile-access', ['mobile'])->plainTextToken;

            Log::info('Mobile token refreshed', [
                'user_id' => $user->id,
                'username' => $user->username,
                'tenant_id' => $user->tenant_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'token' => $newToken,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile token refresh error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during token refresh.',
            ], 500);
        }
    }
}