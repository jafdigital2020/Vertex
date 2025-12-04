<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckCandidatePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permissionId = null): Response
    {
        if (!Auth::guard('candidate')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('career.login');
        }

        $candidate = Auth::guard('candidate')->user();

        // If no specific permission required, just check if authenticated
        if (!$permissionId) {
            return $next($request);
        }

        // Check if candidate has the required permission
        if (!$candidate->hasPermission($permissionId)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }
            return redirect()->route('career.index')
                ->with('error', 'You do not have permission to access this feature.');
        }

        return $next($request);
    }
}
