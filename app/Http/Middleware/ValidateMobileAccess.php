<?php

namespace App\Http\Middleware;

use App\Models\MobileAccessAssignment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateMobileAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to API requests
        if (!$request->is('api/*')) {
            return $next($request);
        }

        $user = $request->user();

        // If no user is authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user has active mobile access license
        $mobileAccess = MobileAccessAssignment::forTenant($user->tenant_id)
            ->forUser($user->id)
            ->active()
            ->first();

        if (!$mobileAccess) {
            // Revoke the current token since access was removed
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            Log::warning('Mobile access denied in middleware - no active license', [
                'user_id' => $user->id,
                'username' => $user->username,
                'tenant_id' => $user->tenant_id,
                'route' => $request->route()->getName(),
                'url' => $request->url(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You do not have mobile access. Please contact your administrator to request access.',
                'error_code' => 'NO_MOBILE_ACCESS',
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        // Check if user account is still active
        if (!$user->active_license) {
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            Log::warning('Mobile access denied in middleware - inactive account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'tenant_id' => $user->tenant_id,
                'route' => $request->route()->getName(),
                'url' => $request->url(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact your administrator.',
                'error_code' => 'ACCOUNT_DEACTIVATED',
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        // Add mobile access info to request for use in controllers
        $request->attributes->set('mobile_access', $mobileAccess);

        return $next($request);
    }
}