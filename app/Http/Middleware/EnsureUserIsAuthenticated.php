<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureUserIsAuthenticated
{ 
    public function handle(Request $request, Closure $next, $guard = null)
    {   

       if (!Auth::guard('web')->check() && !Auth::guard('global')->check()) {

          return redirect()->route('login');
       } 

       return $next($request);
    }
}
