<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('global')->user();  
       
        if (!$user || $user->global_role->global_role_name !== 'super_admin') {
            Log::warning('Access Denied. Not a Super Admin.', ['user_id' => $user?->id, 'role' => $user?->role]);
            return response()->view('errors.access_denied', [], 403); 
        } 
        return $next($request);
    }
}
