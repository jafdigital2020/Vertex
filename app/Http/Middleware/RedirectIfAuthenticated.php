<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Check if this is an API request
        if ($request->expectsJson() || $request->is('api/*')) {
            return $next($request);
        }

        // For web requests, check authentication
        if (Auth::guard('web')->check()) {
            return redirect()->route('employee-dashboard');
        }

        if (Auth::guard('global')->check()) {
            $globalUser = Auth::guard('global')->user();

            // Add null safety
            if ($globalUser && $globalUser->global_role) {
                if ($globalUser->global_role->global_role_name == 'super_admin') {
                    return redirect()->route('superadmin-dashboard');
                } else {
                    return redirect()->route('admin-dashboard');
                }
            }
        }

        return $next($request);
    }
}
